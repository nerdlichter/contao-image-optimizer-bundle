<?php

namespace Nerdlichter\ImageOptimizerBundle\Command;

use InvalidArgumentException;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class OptimizeImagesCommand
 * @package Nerdlichter\ImageOptimizerBundle\Command
 */
class OptimizeImagesCommand extends Command
{
    /**
     * @var string
     */
    private const BACKUP_SUFFIX = '.original';
    /**
     * @var  OutputInterface
     */
    private $output;
    /**
     * @var array
     */
    private $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    /**
     * @var int
     */
    private $originalSize = 0;
    /**
     * @var int
     */
    private $optimizedSize = 0;
    /**
     * @var OptimizerChain
     */
    private $optimizer;
    /**
     * @var bool
     */
    private $dryRun;
    /**
     * @var bool
     */
    private $backup;
    /**
     * @var ContaoFramework
     */
    private $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('nl-image:optimize')
            ->setDescription('Optimize images by path')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('path', 'p', InputOption::VALUE_REQUIRED, 'The dir or file path to use'),
                    new InputOption('dry-run', 't', InputOption::VALUE_OPTIONAL, 'Execute a dry-run', false),
                    new InputOption('backup', 'b', InputOption::VALUE_OPTIONAL, 'Create ".original" backup file', false),
                ])
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->framework->initialize();

        $path = $this->init($input, $output);
        $this->process($path);
        $this->printInformation();

        return 0;
    }

    /**
     * @param string $filePath
     */
    private function optimizeImage(string $filePath)
    {
        $this->output->writeln(sprintf('Optimizing <info>%s</info>', $filePath));

        $this->updateOriginalSize($filePath);

        $outputFilePath = $this->getOutputPath($filePath);
        $this->backupFile($filePath);
        $this->optimize($filePath, $outputFilePath);

        $this->updateOptimizedSize($outputFilePath);
    }

    /**
     * @param $path
     * @return Finder
     */
    private function createFinder($path): Finder
    {
        $this->output->writeln(sprintf('Using path "%s"', $path));

        $finder = Finder::create()->files()->in($path);

        foreach ($this->imageExtensions as $extension) {
            $extension = sprintf('*.%s', $extension);
            $finder->name($extension);
        }

        $this->output->writeln(sprintf('Found %s images', $finder->count()));

        return $finder;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|string|string[]|null
     */
    private function init(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->optimizer = OptimizerChainFactory::create();

        $this->dryRun = $input->getOption('dry-run') !== false;
        $this->backup = $input->getOption('backup') !== false;

        $path = $input->getOption('path');

        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('The specified path "%s" does not exist!', $path));
        }

        if ($this->dryRun) {
            $this->output->writeln('!! Performing dry-run, no changes will be made !!');
        }

        return $path;
    }

    /**
     * @param $path
     */
    private function process($path): void
    {
        //handle single file
        if (is_file($path)) {
            $this->optimizeImage($path);
            return;
        }

        //handle finder results
        $files = $this->createFinder($path);
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $this->optimizeImage($file->getRealPath());
        }
    }

    /**
     * @param string $filePath
     * @return string
     */
    private function getOutputPath(string $filePath): string
    {
        $outputFilePath = $filePath;

        if ($this->dryRun) {
            $tmpFile = tmpfile();
            $outputFilePath = stream_get_meta_data($tmpFile)['uri'];
        }

        return $outputFilePath;
    }

    /**
     * @param string $filePath
     */
    private function backupFile(string $filePath): void
    {
        $backupFilePath = $filePath . self::BACKUP_SUFFIX;
        $shouldBackup = !$this->dryRun && $this->backup;
        if ($shouldBackup && copy($filePath, $backupFilePath)) {
            $this->output->writeln(sprintf('Created Backup <info>%s</info>', $backupFilePath));
        }
    }

    private function printInformation(): void
    {
        $this->output->writeln(sprintf('Total Bytes: <info>%s</info> / <info>%s</info>', $this->originalSize, $this->optimizedSize));

        $this->output->writeln(sprintf(
                'Change Percentage: <info>%s</info>',
                number_format($this->optimizedSize / $this->originalSize * 100, 2) . '%')
        );
    }

    /**
     * @param string $filePath
     * @return false|int
     */
    private function updateOriginalSize(string $filePath)
    {
        return $this->originalSize += filesize($filePath);
    }

    /**
     * @param string $outputFilePath
     * @return false|int
     */
    private function updateOptimizedSize(string $outputFilePath)
    {
        return $this->optimizedSize += filesize($outputFilePath);
    }

    /**
     * @param string $filePath
     * @param string $outputFilePath
     */
    private function optimize(string $filePath, string $outputFilePath): void
    {
        $this->optimizer->optimize($filePath, $outputFilePath);
    }
}

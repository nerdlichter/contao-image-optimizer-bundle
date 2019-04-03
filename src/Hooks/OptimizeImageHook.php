<?php

namespace Nerdlichter\ImageOptimizerBundle\Hooks;

use Contao\System;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class OptimizeImageHook
{
    /**
     * @var OptimizerChain
     */
    private $optimizer;

    /**
     * OptimizeImageHook constructor.
     */
    public function __construct()
    {
        $this->optimizer = OptimizerChainFactory::create();
    }

    /**
     * @param $files
     */
    public function postUpload($files)
    {
        $rootPath = System::getContainer()->get('kernel')->getProjectDir();

        foreach ($files as $file) {
            $filePath = sprintf('%s/%s', $rootPath, $file);
            if ($this->isImage($filePath)) {
                $this->optimize($filePath);
            }
        }
    }

    /**
     * @param $file
     * @return bool
     */
    private function isImage($file)
    {
        return false !== exif_imagetype($file);
    }

    /**
     * @param string $filePath
     * @param int $originalSize
     * @param int $newFileSize
     */
    private function log(string $filePath, int $originalSize, int $newFileSize): void
    {
        $line = sprintf('Optimized "%s": Bytes %s -> %s (%s%%)',
            basename($filePath),
            $originalSize,
            $newFileSize,
            number_format($newFileSize / $originalSize * 100, 2)
        );

        log_message($line, 'image-upload-optimizer.log');
    }

    /**
     * @param string $filePath
     * @param string|null $pathToOutput
     */
    private function optimize(string $filePath, string $pathToOutput = null): void
    {
        $originalSize = filesize($filePath);
        $this->optimizer->optimize($filePath, $pathToOutput);
        $newFileSize = filesize($pathToOutput ?: $filePath);
        $this->log($filePath, $originalSize, $newFileSize);
    }
}

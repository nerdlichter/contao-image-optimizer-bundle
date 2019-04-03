<?php

namespace Nerdlichter\ImageOptimizerBundle;

use Nerdlichter\ImageOptimizerBundle\DependencyInjection\ImageOptimizerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoImageOptimizerBundle extends Bundle
{
    /**
     * Register extension
     */
    public function getContainerExtension()
    {
        return new ImageOptimizerExtension();
    }
}

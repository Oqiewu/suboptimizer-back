<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        if ($this->getEnvironment() === 'dev') {
            return sys_get_temp_dir().'/symfony_cache/'.$this->getEnvironment();
        }

        return parent::getCacheDir();
    }
}


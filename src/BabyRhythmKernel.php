<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class BabyRhythmKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache/baby-rhythm/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/log/baby-rhythm';
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getConfigDir().'/routes_baby_rhythm.yaml');
    }
}

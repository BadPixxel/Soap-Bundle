<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2018 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function registerBundles()
    {
        //==============================================================================
        // SYMFONY CORE
        $bundles[] = new Symfony\Bundle\FrameworkBundle\FrameworkBundle();
        $bundles[] = new Symfony\Bundle\SecurityBundle\SecurityBundle();
        $bundles[] = new Symfony\Bundle\TwigBundle\TwigBundle();
        $bundles[] = new Symfony\Bundle\MonologBundle\MonologBundle();
        
        //==============================================================================
        // DOCTRINE CORE
        $bundles[] = new Doctrine\Bundle\DoctrineBundle\DoctrineBundle();
        
        //==============================================================================
        // SPLASH PHP BUNDLE
        $bundles[] = new Splash\Bundle\SplashBundle();

        //==============================================================================
        // SPLASH CONNECTORS BUNDLE
        $bundles[] = new Splash\Connectors\SoapBundle\SplashSoapBundle();
        
        //==============================================================================
        // TEST & DEV BUNDLES
        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();

            if ('dev' === $this->getEnvironment()) {
                $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            }
            
            if (('dev' === $this->getEnvironment()) && class_exists("\\Symfony\\Bundle\\WebServerBundle\\WebServerBundle")) {
                $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
            }
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        if ("test" == $this->getEnvironment()) {
            $loader->load($this->getRootDir().'/config_test.yml');

            return;
        }
        $loader->load($this->getRootDir().'/config.yml');
    }
}

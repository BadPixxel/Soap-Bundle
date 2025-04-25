<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

return array(
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => array('all' => true),
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => array('all' => true),
    Symfony\Bundle\MonologBundle\MonologBundle::class => array('all' => true),
    Splash\Bundle\SplashBundle::class => array('all' => true),
    Splash\Connectors\Faker\FakerBundle::class => array('all' => true),
    Splash\Connectors\Soap\SoapBundle::class => array('all' => true),
    Symfony\Bundle\TwigBundle\TwigBundle::class => array('all' => true),
);

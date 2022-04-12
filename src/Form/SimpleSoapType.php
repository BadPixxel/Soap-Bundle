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

namespace Splash\Connectors\Soap\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form Type for Generics Soap Connectors Servers
 */
class SimpleSoapType extends AbstractSoapType
{
    /**
     * Build Simple SOAP Node Edit Form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this
            ->addWsHost($builder)
            ->addWsPath($builder)
            ->addEnableHttps($builder)
            ->addHttpAuth($builder)
            ;
    }
}

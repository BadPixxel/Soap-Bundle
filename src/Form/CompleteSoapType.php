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
 * Form Type for Extended Soap Connectors Servers
 */
class CompleteSoapType extends AbstractSoapType
{
    /**
     * Build Complete SOAP Node Edit Form (with Ws Id & Key)
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
            ->addWsIdentifiers($builder)
            ->addEnableHttps($builder)
            ->addHttpAuth($builder)
            ;
    }
}

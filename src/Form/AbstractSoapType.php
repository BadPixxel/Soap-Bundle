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

namespace Splash\Connectors\Soap\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Base Form Type for Soap Connectors Servers
 */
abstract class AbstractSoapType extends AbstractType
{
    /**
     * Add Remote Host Url Field.
     *
     * @param FormBuilderInterface $builder
     *
     * @return self
     */
    protected function addWsHost(FormBuilderInterface $builder)
    {
        $builder
            ->add('WsHost', TextType::class, array(
                'label' => 'form.host.label',
                'help_block' => 'form.host.desc',
                'translation_domain' => 'SoapBundle',
            ))
            ;

        return $this;
    }

    /**
     * Add Remote Path Field.
     *
     * @param FormBuilderInterface $builder
     *
     * @return self
     */
    protected function addWsPath(FormBuilderInterface $builder)
    {
        $builder
            ->add('WsPath', TextType::class, array(
                'label' => 'form.folder.label',
                'help_block' => 'form.folder.desc',
                'translation_domain' => 'SoapBundle',
            ))
            ;

        return $this;
    }

    /**
     * Add Enable HTTPS Field.
     *
     * @param FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addEnableHttps(FormBuilderInterface $builder)
    {
        $builder
            //==============================================================================
            // Https Url Option Authentification
            ->add('EnableHttps', self::getCheckBoxClass(), array(
                'label' => 'form.https.label',
                'help_block' => 'form.https.desc',
                'required' => false,
                'translation_domain' => 'SoapBundle',
            ))
            ;

        return $this;
    }

    /**
     * Add Ws Identification Fields.
     *
     * @param FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addWsIdentifiers(FormBuilderInterface $builder)
    {
        $builder
            //==============================================================================
            // Http Authentification
            ->add('WsIdentifier', TextType::class, array(
                'label' => 'form.id.label',
                'help_block' => 'form.id.desc',
                'translation_domain' => 'SoapBundle',
            ))

            //==============================================================================
            // Http Authentification -> User
            ->add('WsEncryptionKey', TextType::class, array(
                'label' => 'form.key.label',
                'help_block' => 'form.key.desc',
                'translation_domain' => 'SoapBundle',
            ))
            ;

        return $this;
    }

    /**
     * Add HTTP Fields to FormBuilder.
     *
     * @param FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addHttpAuth(FormBuilderInterface $builder)
    {
        $builder
            //==============================================================================
            // Http Authentification
            ->add('HttpAuth', self::getCheckBoxClass(), array(
                'label' => 'form.httpauth.label',
                'help_block' => 'form.httpauth.desc',
                'translation_domain' => 'SoapBundle',
                'required' => false,
            ))

            //==============================================================================
            // Http Authentification -> User
            ->add('HttpUser', TextType::class, array(
                'label' => 'form.httpuser.label',
                'translation_domain' => 'SoapBundle',
                'required' => false,
            ))

            //==============================================================================
            // Http Authentification -> Password
            ->add('HttpPassword', TextType::class, array(
                'label' => 'form.httppwd.label',
                'translation_domain' => 'SoapBundle',
                'required' => false,
            ))
            ;

        return $this;
    }
    
    /**
     * Detect CheckBox For Type to use
     *
     * @return string
     */
    private static function getCheckBoxClass() : string
    {
        if (class_exists("ThemeBundle\\Form\\Type\\StyledCheckBoxType")) {
            return "ThemeBundle\\Form\\Type\\StyledCheckBoxType";
        }
        
        return CheckboxType::class;
    }
}

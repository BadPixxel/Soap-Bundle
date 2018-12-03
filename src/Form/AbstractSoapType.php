<?php

namespace Splash\Connectors\Soap\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

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
                'help' => 'form.host.desc',
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
                'help' => 'form.folder.desc',
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
            ->add('EnableHttps', CheckboxType::class, array(
                'label' => 'form.https.label',
                'help' => 'form.https.desc',
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
                'help' => 'form.id.desc',
                'translation_domain' => 'SoapBundle',
            ))

            //==============================================================================
            // Http Authentification -> User
            ->add('WsEncryptionKey', TextType::class, array(
                'label' => 'form.key.label',
                'help' => 'form.key.desc',
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
            ->add('HttpAuth', CheckboxType::class, array(
                'label' => 'form.httpauth.label',
                'help' => 'form.httpauth.desc',
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
}

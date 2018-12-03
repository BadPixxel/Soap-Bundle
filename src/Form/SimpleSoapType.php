<?php

namespace Splash\Connectors\Soap\Form;

use Symfony\Component\Form\FormBuilderInterface;


class SimpleSoapType extends AbstractSoapType
{
    /**
     * Build Simple SOAP Node Edit Form
     * 
     * @param FormBuilderInterface $builder
     * @param array $options
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this
                ->addWsHost($builder)
                ->addWsPath($builder)
                ->addEnableHttps($builder)
                ->addHttpAuth($builder)
            ;
    }
}

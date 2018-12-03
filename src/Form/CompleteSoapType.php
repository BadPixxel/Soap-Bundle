<?php

namespace Splash\Connectors\Soap\Form;

use Symfony\Component\Form\FormBuilderInterface;


class CompleteSoapType extends AbstractSoapType
{
    /**
     * Build Complete SOAP Node Edit Form (with Ws Id & Key)
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
                ->addWsIdentifiers($builder)
                ->addEnableHttps($builder)
                ->addHttpAuth($builder)
            ;
    }
}

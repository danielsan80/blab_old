<?php

namespace Dan\ShareBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Dan\CoreBundle\Form\DataTransformer\ArrayToJsonTransformer;

class ShareDataType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ArrayToJsonTransformer();
        $builder
            ->add('route', 'hidden')
            ->add(
                $builder->create('params', 'hidden')
                    ->addModelTransformer($transformer)
            )
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dan\ShareBundle\Model\ShareData'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dan_share_share_data';
    }
}

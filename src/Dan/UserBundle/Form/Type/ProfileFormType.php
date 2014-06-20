<?php

namespace Dan\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseProfileFormType;

class ProfileFormType extends BaseProfileFormType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('current_password');
        $builder->remove('email');
    }

    /**
     * getName
     * 
     * @return string
     */
    public function getName()
    {
        return 'dan_user_profile_account';
    }

}

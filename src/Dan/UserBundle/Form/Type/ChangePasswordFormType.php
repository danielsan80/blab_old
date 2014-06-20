<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dan\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ChangePasswordFormType as BaseChangePasswordFormType;

class ChangePasswordFormType extends BaseChangePasswordFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('current_password');
        $builder->remove('new');
        $builder->add('new', 'password', array('label' => 'Password:'));
    }
    
    public function getName()
    {
        return 'dan_user_change_password';
    }
}

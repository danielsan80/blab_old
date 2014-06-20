<?php

namespace Dan\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    
    private $userManager;
    
    public function setUserManager($userManager)
    {
        $this->userManager = $userManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $userManager = $this->userManager;

        // add your custom field
        $builder->remove('username');
        $builder->remove('plainPassword');
        $builder->add('plainPassword','password', array('label' => 'Password:'));
        
        $builder->addEventListener(FormEvents::PRE_BIND, function (FormEvent $event) use ($userManager) {
            
            $form = $event->getForm();
            $data = $event->getData();
            $user = $form->getData();
            preg_match('/^(?P<username>[^@]*)@/', $data['email'], $matches);
            $username = $matches['username'];
            
            $i='';
            while($userManager->findUserBy(array('username' => $username.$i))) {
                $i++;
            }            
            $user->setUsername($username.$i);
        });
    }
    
    public function getName()
    {
        return 'dan_user_registration';
    }
}

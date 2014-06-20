<?php

namespace Iabadabadu\UserBundle\Form\Handler;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Form\Handler\ProfileFormHandler as BaseProfileFormHandler;

use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;

class ProfileFormHandler extends BaseProfileFormHandler
{
    protected $mailer;
    protected $tokenGenerator;
    
    public function __construct(FormInterface $form, Request $request, UserManagerInterface $userManager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        parent::__construct($form, $request, $userManager);
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
    }
    
    public function process(UserInterface $user, $confirmation = false)
    {
        $this->form->setData($user);

        if ('POST' === $this->request->getMethod()) {
            $oldEmail = $user->getEmail();
            $this->form->bindRequest($this->request);

            if ($this->form->isValid()) {
                $this->danOnSuccess($user, $confirmation && ($oldEmail != $user->getEmail()));

                return true;
            }

            $this->userManager->reloadUser($user);
        }

        return false;
    }
    
    protected function danOnSuccess(UserInterface $user, $confirmation)
    {
        
        
        if ($confirmation) {
            $user->setEnabled(false);
            $user->setMetadata('email_edit',true);
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $this->mailer->sendConfirmationEmailMessage($user);
        }

        parent::onSuccess($user);
    }

}

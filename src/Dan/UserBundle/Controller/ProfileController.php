<?php

namespace Dan\UserBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Dan\CoreBundle\Controller\Controller;

/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{

    /**
     * @Route("/redirect", name="fos_user_profile_show")
     */
    public function fosShowAction()
    {
        return $this->redirect($this->generateUrl('sonata_user_profile_show'));
    }
    
    public function showAction()
    {
        if ($user = $this->getUser()) {
            return $this->redirect($this->generateUrl('sonata_user_profile_edit'));
        }
        
        return parent::showAction();
    }

    /**
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function editAction()
    {
        $user = $this->getUser();
        
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        
        $accountForm = $this->getAccountForm($user);
        $passwordChangeForm = $this->getPasswordChangeForm($user);
        
        return $this->render('DanUserBundle:Profile:edit.html.twig', array(
            'user' => $user,
            'accountForm' => $accountForm->createView(),
            'changePasswordForm' => $passwordChangeForm->createView(),
        ));
    }

    /**
     * @param string $action
     * @param string $value
     */
    protected function setFlash($action, $value)
    {
        $this->container->get('session')->setFlash($action, $value);
    }
    
    protected function getRequestDataFormName()
    {
        $request = $this->getRequest();
        $data = $request->request->all();
        if (count($data)) {
            $typeName = array_keys($data);
            return $typeName[0];
        }
        return null;       
    }
    
    protected function getAccountForm($user)
    {
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');
        
        $form = $this->container->get('fos_user.profile.form');
        $form->setData($user);


        $form->add('submit', 'submit', array(
            'label' => 'Save',
        ));

        $formHandler = $this->container->get('fos_user.profile.form.handler');
        
        if ($this->getRequestDataFormName()==$form->getName()) {
            $process = $formHandler->process($user, $confirmationEnabled);
            
            if ($process) {
                $this->get('session')->getFlashBag()->add('info','profile.flash.updated');
            }
        }
        return $form;
    }
    
    protected function getPasswordChangeForm($user)
    {
        $form = $this->get('fos_user.change_password.form');
        $formHandler = $this->get('fos_user.change_password.form.handler');

        $form->add('submit', 'submit', array(
            'label' => 'Change',
        ));

        if ($this->getRequestDataFormName()==$form->getName()) {
            $process = $formHandler->process($user);
            if ($process) {
                $this->get('session')->getFlashBag()->add('info', 'change_password.flash.success');
            }
        }
        
        return $form;
    }
}

<?php

namespace Dan\ShareBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Dan\CoreBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Dan\ShareBundle\Model\ShareData;
use Dan\ShareBundle\Form\ShareDataType;

/**
 * Widget controller.
 * @Route("/share")
 */
class WidgetController extends Controller
{

    /**
     * @Template
     */
    public function shareLinkAction(Request $request, ShareData $shareData)
    {

        $id = $shareData->getToken();

        $formCreate = $this->createCreateForm($shareData);

        $data =  array(
            'owner' => $shareData->isOwner($this->getUser()),
            'token' => $shareData->getToken(),
            'form_create' => $formCreate->createView(),
        );

        if ($id) {
            $formDelete = $this->createDeleteForm($id);
            $data['form_delete'] = $formDelete->createView();

            $formReset = $this->createResetForm($id);
            $data['form_reset'] = $formReset->createView();
        }


        return $data;
    }

    /**
     * @Route("/create", name="share_token_create")
     * @Method("POST")
     */
    public function createShareTokenAction(Request $request)
    {
        $this->givenUserIsLoggedIn();
        $user = $this->getUser();

        $shareData = new ShareData();
        $shareData->setUser($user);

        $form = $this->createCreateForm($shareData);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $sharer = $this->get('sharer');

            $route = $shareData->getRoute();
            $params = $shareData->getParams();
            $shareToken = $sharer->createShareToken($user, $route, $params);

        }

        // This should not happen
        $route = $shareData->getRoute();
        $params = $shareData->getParams();
        if ($route && is_array($params)) {
            return $this->redirect($this->generateUrl($route, $params));
        }

        throw new BadRequestHttpException();
    }

    /**
     * @Route("/{share_token}", name="share_token_delete")
     * @Method("DELETE")
     */
    public function deleteShareTokenAction(Request $request, $share_token)
    {
        $this->givenUserIsLoggedIn();
        $user = $this->getUser();
        $id = $share_token;

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {

            $sharer = $this->get('sharer');

            $shareToken = $sharer->getShareTokenById($id);

            if ($user->getId()!=$shareToken->getUser()->getId())
            {
                throw $this->createAccessDeniedException('You do not own this token');
            }

            $route = $shareToken->getRoute();
            $params = $shareToken->getParams();

            $sharer->removeShareToken($shareToken);

            return $this->redirect($this->generateUrl($route, $params));
        }

        throw new BadRequestHttpException();
    }

    /**
     * @Route("/{share_token}/reset", name="share_token_reset")
     * @Method("POST")
     */
    public function resetShareTokenAction(Request $request, $share_token)
    {
        $this->givenUserIsLoggedIn();
        $user = $this->getUser();
        $id = $share_token;

        $form = $this->createResetForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {

            $sharer = $this->get('sharer');

            $shareToken = $sharer->getShareTokenById($id);

            if ($user->getId()!=$shareToken->getUser()->getId())
            {
                throw $this->createAccessDeniedException('You do not own this token');
            }

            $route = $shareToken->getRoute();
            $params = $shareToken->getParams();

            $sharer->resetShareToken($shareToken);

            return $this->redirect($this->generateUrl($route, $params));
        }

        throw new BadRequestHttpException();
    }

    private function createCreateForm(ShareData $shareData)
    {
        $form = $this->createForm(new ShareDataType(), $shareData, array(
            'action' => $this->generateUrl('share_token_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    private function createDeleteForm($id, $options = array())
    {
        $defaults = array(
            'label' => 'Delete',
            'class' => '',
        );
        $options = array_merge($defaults, $options);

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('share_token_delete', array('share_token' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array(
                'attr' => array('class' =>$options['class']),
                'label' => $options['label']
            ))
            ->getForm()
        ;
    }

    private function createResetForm($id, $options = array())
    {
        $defaults = array(
            'label' => 'Reset',
            'class' => '',
        );
        $options = array_merge($defaults, $options);

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('share_token_reset', array('share_token' => $id)))
            ->setMethod('POST')
            ->add('submit', 'submit', array(
                'attr' => array('class' =>$options['class']),
                'label' => $options['label']
            ))
            ->getForm()
        ;
    }
}

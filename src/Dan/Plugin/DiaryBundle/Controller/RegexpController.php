<?php

namespace Dan\Plugin\DiaryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Dan\CoreBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Dan\Plugin\DiaryBundle\Form\GenericDataType;
use Dan\Plugin\DiaryBundle\Model\GenericData;

use Symfony\Component\Yaml\Yaml;

/**
 * Report controller.
 *
 * @Route("/regexp")
 */
class RegexpController extends Controller
{

    /**
     * @Route("/edit", name="diary_regexp_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction()
    {
        $this->givenUserIsSuperAdmin();

        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $defaults = $this->get('dan_diary.regexp.helper')->getDefaultRegexp();

        $data = $userManager->getMetadata($user, 'diary', 'regexp', $defaults);
        $regexpData = new GenericData($data);
        $form = $this->createDataForm($regexpData);
        $formReset = $this->createResetForm();

        return array(
            'form'   => $form->createView(),
            'form_reset'  => $formReset->createView(),
        );
    }

    /**
     * @Route("")
     * @Method("GET")
     */
    public function redirectAction(Request $request)
    {
        return $this->redirect($this->generateUrl('diary_regexp_edit'));
    }

    /**
     * @Route("", name="diary_regexp_update")
     * @Method("PUT")
     * @Template("DanPluginDiaryBundle:Regexp:edit.html.twig")
     */
    public function updateAction(Request $request)
    {
        $this->givenUserIsSuperAdmin();

        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $defaults = $this->get('dan_diary.regexp.helper')->getDefaultRegexp();

        $data = $userManager->getMetadata($user, 'diary', 'regexp', $defaults);
        $regexpData = new GenericData($data);
        $form = $this->createDataForm($regexpData);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $userManager->setMetadata($user, 'diary', 'regexp', $regexpData->getData());
            return $this->redirect($this->generateUrl('regexp_edit'));
        }

        return array(
            'form'   => $form->createView(),
        );
    }
    
    /**
     * @Route("/reset", name="diary_regexp_reset")
     * @Method("POST")
     */
    public function resetAction(Request $request)
    {
        $this->givenUserIsSuperAdmin();

        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $defaults = $this->get('dan_diary.regexp.helper')->getDefaultRegexp();

        $userManager->setMetadata($user, 'diary', 'regexp', $defaults);

        return $this->redirect($this->generateUrl('regexp_edit'));
    }

    private function createDataForm(GenericData $regexpData)
    {
        $form = $this->createForm(new GenericDataType(), $regexpData, array(
            'action' => $this->generateUrl('diary_regexp_update'),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    private function createResetForm()
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('diary_regexp_reset'))
            ->setMethod('POST')
        ;

        $form->add('submit', 'submit', array('label' => 'Reset'));

        return $form->getForm();
    }

}

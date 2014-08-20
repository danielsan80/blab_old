<?php

namespace Dan\Plugin\DiaryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Dan\CoreBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Dan\Plugin\DiaryBundle\Form\RegexpDataType;
use Dan\Plugin\DiaryBundle\Model\RegexpData;

use Symfony\Component\Yaml\Yaml;

/**
 * Report controller.
 *
 * @Route("/regexp")
 */
class RegexpController extends Controller
{

    /**
     * @Route("/edit", name="regexp_edit")
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
        $regexpData = new RegexpData($data);
        $form = $this->createDataForm($regexpData);
        $formReset = $this->createResetForm();

        return array(
            'form'   => $form->createView(),
            'form_reset'  => $formReset->createView(),
        );
    }

    /**
     * @Route("", name="regexp_update")
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
        $regexpData = new RegexpData($data);
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
     * @Route("/reset", name="regexp_reset")
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

    private function createDataForm(RegexpData $regexpData)
    {
        $form = $this->createForm(new RegexpDataType(), $regexpData, array(
            'action' => $this->generateUrl('regexp_update'),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    private function createResetForm()
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('regexp_reset'))
            ->setMethod('POST')
        ;

        $form->add('submit', 'submit', array('label' => 'Reset'));

        return $form->getForm();
    }

}

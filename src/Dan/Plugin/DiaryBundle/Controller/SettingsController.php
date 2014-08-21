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
 * @Route("/settings")
 */
class SettingsController extends Controller
{

    /**
     * @Route("/edit", name="diary_settings_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction()
    {
        $this->givenUserIsSuperAdmin();

        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $data = $userManager->getMetadata($user, 'diary', 'settings', array());
        $settingsData = new GenericData($data);
        $form = $this->createDataForm($settingsData);

        return array(
            'form'   => $form->createView(),
        );
    }

    /**
     * @Route("")
     * @Method("GET")
     */
    public function redirectAction(Request $request)
    {
        return $this->redirect($this->generateUrl('diary_settings_edit'));
    }

    /**
     * @Route("", name="diary_settings_update")
     * @Method("PUT")
     * @Template("DanPluginDiaryBundle:Settings:edit.html.twig")
     */
    public function updateAction(Request $request)
    {
        $this->givenUserIsSuperAdmin();

        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $data = $userManager->getMetadata($user, 'diary', 'settings', array());
        $settingsData = new GenericData($data);
        $form = $this->createDataForm($settingsData);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $userManager->setMetadata($user, 'diary', 'settings', $settingsData->getData());
            return $this->redirect($this->generateUrl('diary_settings_edit'));
        }

        return array(
            'form'   => $form->createView(),
        );
    }

    private function createDataForm(GenericData $settingsData)
    {
        $form = $this->createForm(new GenericDataType(), $settingsData, array(
            'action' => $this->generateUrl('diary_settings_update'),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

}

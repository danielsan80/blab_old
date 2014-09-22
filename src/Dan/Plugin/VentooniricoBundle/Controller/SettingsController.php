<?php

namespace Dan\Plugin\VentooniricoBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Dan\CoreBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Dan\CoreBundle\Form\Type\GenericDataType;
use Dan\CoreBundle\Model\GenericData;

use Symfony\Component\Yaml\Yaml;

/**
 * @Route("/settings")
 */
class SettingsController extends Controller
{

    /**
     * @Route("", name="ventoonirico_settings")
     * @Method("GET")
     * @Template()
     */
    public function editAction()
    {
        $this->givenUserIsSuperAdmin();

        $metadataManager = $this->get('model.manager.metadata');

        $data = $metadataManager->getMetadata('ventoonirico', 'settings', array());
        if (!$data) {
            $defaultSettings = $this->get('kernel')->locateResource('@DanPluginVentooniricoBundle/Resources/data/default_settings.yml');
            $defaultSettings = Yaml::parse(file_get_contents($defaultSettings));
            $metadataManager->setMetadata('ventoonirico', 'settings', $defaultSettings);
            $data = $metadataManager->getMetadata('ventoonirico', 'settings', array());
        }
        $settingsData = new GenericData($data);
        $form = $this->createDataForm($settingsData);

        return array(
            'form'   => $form->createView(),
        );
    }

    /**
     * @Route("", name="ventoonirico_settings_update")
     * @Method("PUT")
     * @Template("DanVentooniricoBundle:Settings:edit.html.twig")
     */
    public function updateAction(Request $request)
    {
        $this->givenUserIsSuperAdmin();

        $metadataManager = $this->get('model.manager.metadata');

        $data = $metadataManager->getMetadata('ventoonirico', 'settings', array());
        $settingsData = new GenericData($data);
        $form = $this->createDataForm($settingsData);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $metadataManager->setMetadata('ventoonirico', 'settings', $settingsData->getData());
            return $this->redirect($this->generateUrl('ventoonirico_settings'));
        }

        return array(
            'form'   => $form->createView(),
        );
    }

    private function createDataForm(GenericData $settingsData)
    {
        $form = $this->createForm(new GenericDataType(), $settingsData, array(
            'action' => $this->generateUrl('ventoonirico_settings'),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

}

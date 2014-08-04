<?php

namespace Dan\Plugin\DiaryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $defaults = $this->get('dan_diary.regexp.helper')->getDefaultRegexp();

        $data = $userManager->getMetadata($user, 'diary', null, $defaults);
        $regexpData = new RegexpData($data);
        $form = $this->createDataForm($regexpData);

        return array(
            'form'   => $form->createView(),
        );
    }

    /**
     * @Route("", name="regexp_update")
     * @Method("PUT")
     * @Template("DanPluginDiaryBundle:Regexp:edit.html.twig")
     */
    public function updateAction(Request $request)
    {
        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $defaults = array(
            'regexp' => array(),
        );

        $data = $userManager->getMetadata($user, 'diary', null, $defaults);
        $regexpData = new RegexpData($data);
        $form = $this->createDataForm($regexpData);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $userManager->setMetadata($user, 'diary', $regexpData->getData());
            return $this->redirect($this->generateUrl('regexp_edit'));
        }

        return array(
            'form'   => $form->createView(),
        );
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

}

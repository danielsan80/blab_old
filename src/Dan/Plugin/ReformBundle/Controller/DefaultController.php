<?php

namespace Dan\Plugin\ReformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Dan\CoreBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Dan\CoreBundle\Form\Type\GenericDataType;
use Dan\CoreBundle\Model\GenericData;

use Symfony\Component\Yaml\Yaml;

/**
 * @Route("")
 */
class DefaultController extends Controller
{

    /**
     * @Route("/edit", name="reform_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction()
    {
        $this->givenUserIsLoggedIn();

        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $data = $userManager->getMetadata($user, 'reform', 'data', array());
        $formData = $this->createDataForm($data);


        $def = $userManager->getMetadata($user, 'reform', 'def', $this->getDefaultDef());
        $formDef = $this->createDefForm(new GenericData($def));

        return array(
            'form_data'   => $formData->createView(),
            'form_def'   => $formDef->createView(),
            'yaml'   => Yaml::dump($data,9),
        );
    }

    /**
     * @Route("", name="reform_index")
     * @Method("GET")
     */
    public function redirectAction(Request $request)
    {
        return $this->redirect($this->generateUrl('reform_edit'));
    }

    /**
     * @Route("/data", name="reform_data_update")
     * @Method("PUT")
     * @Template("DanPluginReformBundle:Tmp:edit.html.twig")
     */
    public function updateDataAction(Request $request)
    {
        $this->givenUserIsLoggedIn();

        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $data = $userManager->getMetadata($user, 'reform', 'data', array());
        $formData = $this->createDataForm($data);

        $formData->handleRequest($request);

        if ($formData->isValid()) {

            $userManager->setMetadata($user, 'reform', 'data', $formData->getData());
            return $this->redirect($this->generateUrl('reform_edit'));
        }

        $def = $userManager->getMetadata($user, 'reform', 'def', array());
        $formDef = $this->createDefForm(new GenericData($def));


        return array(
            'form_data'   => $formData->createView(),
            'form_def'   => $formDef->createView(),
            'yaml'   => Yaml::dump($data,9),
        );
    }

    /**
     * @Route("/def", name="reform_def_update")
     * @Method("PUT")
     * @Template("DanPluginReformBundle:Tmp:edit.html.twig")
     */
    public function updateDefAction(Request $request)
    {
        $this->givenUserIsLoggedIn();

        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $def = $userManager->getMetadata($user, 'reform', 'def', array());
        $defData = new GenericData($def);
        $form = $this->createDefForm($defData);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $userManager->setMetadata($user, 'reform', 'def', $defData->getData());
            return $this->redirect($this->generateUrl('reform_edit'));
        }

        $data = $userManager->getMetadata($user, 'reform', 'data', array());
        $formData = $this->createDataForm($data);

        return array(
            'form_data'   => $formData->createView(),
            'form_def'   => $formDef->createView(),
            'yaml'   => Yaml::dump($data,9),
        );

    }

    private function createDataForm($data)
    {
        $builder = $this->createFormBuilder($data);
        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $def = $userManager->getMetadata($user, 'reform', 'def', array());
        foreach($def as $group => $groupData) {
            if (! isset($groupData['label'])) {
                $groupData['label'] = $group;
            }
            if (! isset($groupData['properties'])) {
                $groupData['properties'] = array();
            }
            $form = $builder->create($group, 'form', array('label' => $groupData['label']));
            foreach($groupData['properties'] as $property => $propertyData ) {
                $type = $propertyData['type'];
                $options = array(
                    'label' => $propertyData['label']
                );

                if (preg_match('/^(choice)/', $type)) {
                    $options['choices'] = Yaml::parse(strtr($type, array(
                            'choice[' => '{',
                            ']' => '}',
                        )));
                    $type = 'choice';
                }

                $form->add($property, $type, $options);
            }
            $builder->add($form);
        }

//
//        $builder->add('text', 'text');
//        $builder->add('select', 'choice', array(
//                'choices' => array('yes' => 'Yes', 'no' => 'No')
//            ));
//
//        $subform = $builder->create('anagrafica', 'form');
//        $subform->add('prova');
//        
//        $builder->add($subform);

        $builder->setAction($this->generateUrl('reform_data_update'));
        $builder->setMethod('PUT');
        $builder->add('submit', 'submit', array('label' => 'Update'));

        return $builder->getForm();
    }

    private function createDefForm(GenericData $defData)
    {
        $form = $this->createForm(new GenericDataType(), $defData, array(
            'action' => $this->generateUrl('reform_def_update'),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    private function getDefaultDef() {
        $kernel = $this->get('kernel');
        $yaml = file_get_contents($kernel->locateResource('@DanPluginReformBundle/Resources/data/default_def.yml'));

        return Yaml::parse($yaml);
    }
}

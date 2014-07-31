<?php

namespace Dan\Plugin\DiaryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dan\Plugin\DiaryBundle\Entity\Report;
use Dan\Plugin\DiaryBundle\Form\ReportType;

/**
 * Report controller.
 *
 * @Route("/regexp")
 */
class RegexpController extends Controller
{

    /**
     * Displays a form to edit an existing Report entity.
     *
     * @Route("/edit", name="regexp_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction()
    {
        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');

        $defaults = array();

        $data = $userManager->getMetadata($user, 'diary', 'regexp', $defaults);

        $form = $this->createDataForm($data);

        return array(
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to edit a Report entity.
    *
    * @param Report $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createDataForm(RegexpData $regexpData)
    {
        $form = $this->createForm(new RegexpDataType(), $regexpData, array(
            'action' => $this->generateUrl('regexp_update'),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Report entity.
     *
     * @Route("", name="regexp_update")
     * @Method("PUT")
     * @Template("DanPluginDiaryBundle:Regexp:edit.html.twig")
     */
    public function updateAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DanPluginDiaryBundle:Report')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Report entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('report_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Report entity.
     *
     * @Route("/{id}", name="report_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DanPluginDiaryBundle:Report')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Report entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('report'));
    }

    /**
     * Creates a form to delete a Report entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id, $options = array())
    {
        $defaults = array(
            'label' => 'Delete',
            'class' => '',
        );
        $options = array_merge($defaults, $options);

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('report_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array(
                'attr' => array('class' =>$options['class']),
                'label' => $options['label']
            ))
            ->getForm()
        ;
    }
}

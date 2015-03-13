<?php

namespace Dan\Plugin\DiaryBundle\Controller;

use Dan\CoreBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dan\Plugin\DiaryBundle\Entity\Report;
use Dan\Plugin\DiaryBundle\Form\ReportType;

use Dan\Plugin\DiaryBundle\Model\ReportCollection;

use Symfony\Component\Yaml\Yaml;

use Dan\Plugin\DiaryBundle\Parser\Parser\DefaultParser;

/**
 * Report controller.
 *
 * @Route("/report")
 */
class ReportController extends Controller
{

    /**
     * @Route("/parse", name="report_parse")
     * @Method("POST")
     */
    public function parseReportAction(Request $request)
    {
        $this->givenUserIsLoggedIn();

        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');
        $reportManager = $this->get('dan_diary.model.manager.report');

        $data = json_decode($request->getContent(), true);
        $content = $data['content'];

        $data = $reportManager->parseContentForPreview($content);

        $content = json_encode($data);

        $response = new Response($content, 200, array('Content-Type' => 'application/json; charset=utf-8'));

        return $response;
    }


    /**
     * Lists all Report entities.
     *
     * @Route("/", name="report")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $this->givenUserIsLoggedIn();

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('DanPluginDiaryBundle:Report')->findByUser($user);
        $entities = array_reverse($entities);

        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm($entity->getId(), array(
                'class' =>  'delete',
                'label' => 'del',
            ))->createView();
        }

        return array(
            'entities' => $entities,
            'forms_delete' => $deleteForms,
        );
    }
    
    
    /**
     * Lists all Report entities by month.
     *
     * @Route("/calendar", name="report_calendar")
     * @Method("GET")
     * @Template()
     */
    public function calendarAction()
    {
        
        $this->givenUserIsLoggedIn();
        $request = $this->getRequest();

        $user = $this->getUser();
        $project = $request->get('project');
        $month = $request->get('month');
        
        if (!$month) {
            $now = new \DateTime();
            $month = $now->format('Y-m');
        }
       
        
        $data = $this->getCalendarData($user, $project, $month);
        
        $deleteForms = array();
        
        $arrayHelper = new \Dan\MainBundle\Model\ArrayHelper();
        $pathes = $arrayHelper->explodePath($data, 'reports.*.*.*');
        foreach ($pathes as $path) {
            $report = $arrayHelper->getPath($data, $path);
            $deleteForms[$report->getParent()->getId()] = $this->createDeleteForm($report->getParent()->getId(), array(
                'class' =>  'delete',
                'label' => 'del',
            ))->createView();
        }

        return array_merge($data, array(
            'user' => $user,
            'route' => 'report_calendar',
            'params' => array(),
            'month' => $month,
            'forms_delete' => $deleteForms,
        ));
    }
    
    private function getCalendarData($user, $project, $month)
    {
        
        $arrayHelper = new \Dan\MainBundle\Model\ArrayHelper();
        $data = array();

        $analysisHelper = $this->get('dan_diary.analysis.helper');
        $reportManager = $this->get('dan_diary.model.manager.report');
        
        $reports = $reportManager->getReportsByUser($user);
        $reportChildren = $reportManager->explodeReports($reports);
        
        $_reportChildren = array();

        foreach($reportChildren as $reportChild) {
            if (!$month || $month != $analysisHelper->getMonth($reportChild)) {
                continue;
            }
            
            
            if ($project && $project != $analysisHelper->getProject($reportChild)) {
                continue;
            }
            
            $_reportChildren[] = $reportChild;

        }
        
        $reportChildren = $_reportChildren;
        
        $pager = new \Dan\Plugin\DiaryBundle\Model\Pager\CalendarPager($reportChildren);
        $pager->setDateRetrieveCallback(function($reportChild) use ($analysisHelper) {
            return $analysisHelper->getDate($reportChild);
        });
        $pager->setMonth($month);
        
//        foreach($reports as $report) {
//        
//            $date = $analysisHelper->getDate($report);
//            if (!$date) {
//                continue;
//            }
//            
//            $monthNumber = (int)$date->format('j');
//            $weekNumber = (int)$date->format('W');
//            $dow = (int)$date->format('w');
//            $dow = $dow==0?7:$dow;
//            $dayReports = $arrayHelper->getPath($data, $weekNumber.'.'.$dow, array());
//            $dayReports[] = $report;
//            $data = $arrayHelper->setPath($data, $weekNumber.'.'.$dow, $dayReports);
//        }
        
        return array(
            'project' => $project,
            'month' => $month,
            'reports' => $data,
            'pager' => $pager,
        );
    }
    
    private function createReportCollection($user, $project)
    {
        $userManager = $this->get('model.manager.user');
        $euroPerHour = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.euro_per_hour', null);
        $euroPerDay = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.euro_per_day', null);
        $hoursPerDay = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.hours_per_day', null);

        $collection = new ReportCollection();
        $collection->setMoneyPerHour($euroPerHour);
        $collection->setMoneyPerDay($euroPerDay);
        $collection->setHoursPerDay($hoursPerDay);

        return $collection;
    }

    /**
     * Creates a new Report entity.
     *
     * @Route("/", name="report_create")
     * @Method("POST")
     * @Template("DanPluginDiaryBundle:Report:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->givenUserIsLoggedIn();

        $user = $this->getUser();

        $entity = new Report();
        $entity->setUser($user);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            if ($form->get('submit')->isClicked()) {
                return $this->redirect($this->generateUrl('report'));
            }
            if ($form->get('submitAndContinue')->isClicked()) {
                return $this->redirect($this->generateUrl('report_edit', array('id' => $entity->getId())));
            }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Report entity.
    *
    * @param Report $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Report $entity)
    {
        $form = $this->createForm(new ReportType(), $entity, array(
            'action' => $this->generateUrl('report_create'),
            'method' => 'POST',
        ));

        $form
            ->add('submit', 'submit', array('label' => 'Create'))
            ->add('submitAndContinue', 'submit', array('label' => 'Create and Continue'));

        return $form;
    }

    /**
     * Displays a form to create a new Report entity.
     *
     * @Route("/new", name="report_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $this->givenUserIsLoggedIn();

        $user = $this->getUser();

        $entity = new Report();
        $entity->setUser($user);
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }


    /**
     * Displays a form to edit an existing Report entity.
     *
     * @Route("/{id}/edit", name="report_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $this->givenUserIsLoggedIn();

        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DanPluginDiaryBundle:Report')->find($id);

        if (!$entity || !$this->isOwnedByUser($entity, $user) ) {
            throw $this->createNotFoundException('Unable to find Report entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Report entity.
    *
    * @param Report $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Report $entity)
    {
        $form = $this->createForm(new ReportType(), $entity, array(
            'action' => $this->generateUrl('report_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form
            ->add('submit', 'submit', array('label' => 'Update'))
            ->add('submitAndContinue', 'submit', array('label' => 'Update and Continue'));

        return $form;
    }
    /**
     * Edits an existing Report entity.
     *
     * @Route("/{id}", name="report_update")
     * @Method("PUT")
     * @Template("DanPluginDiaryBundle:Report:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $this->givenUserIsLoggedIn();

        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DanPluginDiaryBundle:Report')->find($id);

        if (!$entity || !$this->isOwnedByUser($entity, $user) ) {
            throw $this->createNotFoundException('Unable to find Report entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            if ($editForm->get('submit')->isClicked()) {
                return $this->redirect($this->generateUrl('report'));
            }
            if ($editForm->get('submitAndContinue')->isClicked()) {
                return $this->redirect($this->generateUrl('report_edit', array('id' => $id)));
            }
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
        $this->givenUserIsLoggedIn();

        $user = $this->getUser();

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DanPluginDiaryBundle:Report')->find($id);

            if (!$entity || !$this->isOwnedByUser($entity, $user)) {
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

    private function isOwnedByUser($entity, $user)
    {
        return $entity->getUser()->getId() == $user->getId();
    }
}

<?php

namespace Dan\Plugin\DiaryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dan\Plugin\DiaryBundle\Entity\Report;

use Symfony\Component\Yaml\Yaml;

/**
 * Analysis controller.
 *
 * @Route("/analysis")
 */
class AnalysisController extends Controller
{

    /**
     * @Route("/project_month", name="analysis_project_month")
     * @Method("GET")
     * @Template
     */
    public function projectMonthAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $helper = $this->get('dan_diary.analysis.helper');

        $reports = $em->getRepository('DanPluginDiaryBundle:Report')->findAll();

        $project = $request->query->get('project');
        $month = $request->query->get('month');
        $seconds = 0;
        foreach($reports as $report) {
            $reportProject = $helper->getProject($report);
            if ($project != $reportProject) {
                continue;
            }

            $reportMonth = $helper->getMonth($report);
            if ($month != $reportMonth) {
                continue;
            }

            $seconds += $helper->getTotalTime($report);
        }

        $hours = $helper->getHours($seconds);


        return array(
            'project' => $project,
            'month' => $month,
            'hours' => $hours,
        );
    }
}

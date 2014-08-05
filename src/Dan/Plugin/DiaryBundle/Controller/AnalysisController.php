<?php

namespace Dan\Plugin\DiaryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Dan\CoreBundle\Controller\Controller;
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
        $this->givenUserIsLoggedIn();

        $user = $this->getUser();
        
        $em = $this->getDoctrine()->getManager();

        $helper = $this->get('dan_diary.analysis.helper');

        $reports = $em->getRepository('DanPluginDiaryBundle:Report')->findByUser($user);

        $project = $request->query->get('project');
        $month = $request->query->get('month');
        $monthlySeconds = 0;

        $_reports = array();
        foreach($reports as $report) {
            $reportProject = $helper->getProject($report);
            if ($project != $reportProject) {
                continue;
            }

            $reportMonth = $helper->getMonth($report);
            if ($month != $reportMonth) {
                continue;
            }

            $dailySeconds = $helper->getTotalTime($report);

            $date = $helper->getDate($report);
            
            $weekNumber = (int)$date->format('W');
            $dow = (int)$date->format('w');

            if (!isset($_reports[$weekNumber])) {
                $_reports[$weekNumber] = array(
                    'seconds' => 0,
                    'reports' => array(),
                );
            }
            if (!isset($_reports[$weekNumber]['reports'][$dow])) {
                $_reports[$weekNumber]['reports'][$dow] = array(
                    'seconds' => 0,
                    'hours' => '0.00',
                    'date' => $date,
                    'reports' => array(),
                );
            }

            $monthlySeconds += $dailySeconds;
            $_reports[$weekNumber]['seconds'] += $dailySeconds;
            $_reports[$weekNumber]['reports'][$dow]['seconds'] += $dailySeconds;
            $_reports[$weekNumber]['reports'][$dow]['hours'] = $helper->getAsHours($_reports[$weekNumber]['reports'][$dow]['seconds']);

            $_reports[$weekNumber]['reports'][$dow]['reports'][] = $report;

        }

        $reports = $_reports;

        ksort($reports);
        foreach($reports as $weekNumber => $week) {
            ksort($week['reports']);
            $reports[$weekNumber] = $week;
        }
        
        return array(
            'project' => $project,
            'month' => $month,
            'monthlyHours' => $helper->getAsHours($monthlySeconds),
            'monthlySeconds' => $monthlySeconds,
            'reports' => $reports,

            'user' => $user,
            'route' => 'analysis_project_month',
            'params' => array(
                    'month' => $month,
                    'project' => $project,
                )

        );
    }
}

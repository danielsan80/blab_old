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
     * @Route("/project_month_resume", name="analysis_project_month_resume")
     * @Method("GET")
     * @Template
     */
    public function projectMonthResumeAction(Request $request)
    {
        $sharer = $this->get('sharer');

        if (!($user = $sharer->getUserFromRequest($request))) {
            $this->givenUserIsLoggedIn();
            $user = $this->getUser();
        }


        $project = $request->get('project');
        $month = $request->get('month');

        $data = $this->getProjectMonthData($user, $project, $month);
        
        return array_merge( $data, array(
            'shareData' => $sharer->createShareData(
                    $user,
                    'analysis_project_month_resume',
                    array(
                        'month' => $month,
                        'project' => $project,
                    )
                ),
            )
        );
    }


    /**
     * @Route("/project_month_diary", name="analysis_project_month_diary")
     * @Method("GET")
     * @Template
     */
    public function projectMonthDiaryAction(Request $request)
    {
        $sharer = $this->get('sharer');

        if (!($user = $sharer->getUserFromRequest($request))) {
            $this->givenUserIsLoggedIn();
            $user = $this->getUser();
        }


        $project = $request->get('project');
        $month = $request->get('month');

        $data = $this->getProjectMonthData($user, $project, $month);

        
        return array_merge( $data, array(
            'shareData' => $sharer->createShareData(
                    $user,
                    'analysis_project_month_diary',
                    array(
                        'month' => $month,
                        'project' => $project,
                    )
                ),
            )
        );
    }

    private function getProjectMonthData($user, $project, $month)
    {
        $em = $this->getDoctrine()->getManager();

        $helper = $this->get('dan_diary.analysis.helper');

        $reports = $em->getRepository('DanPluginDiaryBundle:Report')->findByUser($user);

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
                    'tasks' => array(),
                );
            }

            $monthlySeconds += $dailySeconds;
            $_reports[$weekNumber]['seconds'] += $dailySeconds;
            $_reports[$weekNumber]['reports'][$dow]['seconds'] += $dailySeconds;
            $_reports[$weekNumber]['reports'][$dow]['hours'] = $helper->getAsHours($_reports[$weekNumber]['reports'][$dow]['seconds']);

            $_reports[$weekNumber]['reports'][$dow]['reports'][] = $report;

            $_reports[$weekNumber]['reports'][$dow]['tasks'] = array_merge(
                $_reports[$weekNumber]['reports'][$dow]['tasks'],
                $helper->getTasks($report)
            );

        }

        $reports = $_reports;

        ksort($reports);
        foreach($reports as $weekNumber => $week) {
            ksort($week['reports']);
            $reports[$weekNumber] = $week;
        }

        $userManager = $this->get('model.manager.user');

        $monthlyHours = $helper->getAsHours($monthlySeconds);
        $euroPerHour = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.euro_per_hour', null);
        $monthlyEuro = $monthlyHours * $euroPerHour;
        
        return array(
            'project' => $project,
            'month' => $month,
            'monthlyHours' => $monthlyHours,
            'monthlySeconds' => $monthlySeconds,
            'monthlyEuro' => $monthlyEuro,
            'reports' => $reports,
        );
    }
}

<?php

namespace Dan\Plugin\DiaryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Dan\CoreBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dan\Plugin\DiaryBundle\Entity\Report;
use Dan\Plugin\DiaryBundle\Model\ReportCollection;

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
     * @Route("/project_resume", name="analysis_project_resume")
     * @Method("GET")
     * @Template
     */
    public function projectResumeAction(Request $request)
    {
        $sharer = $this->get('sharer');

        if (!($user = $sharer->getUserFromRequest($request))) {
            $this->givenUserIsLoggedIn();
            $user = $this->getUser();
        }


        $project = $request->get('project');

        $data = $this->getProjectData($user, $project);
        
        return array_merge( $data, array(
            'shareData' => $sharer->createShareData(
                    $user,
                    'analysis_project_resume',
                    array(
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
        $userManager = $this->get('model.manager.user');
        $euroPerHour = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.euro_per_hour', null);
        $euroPerDay = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.euro_per_day', null);
        $hoursPerDay = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.hours_per_day', null);

        $collection = new ReportCollection();
        $collection->setMoneyPerHour($euroPerHour);
        $collection->setMoneyPerDay($euroPerDay);
        $collection->setHoursPerDay($hoursPerDay);

        $em = $this->getDoctrine()->getManager();
        $helper = $this->get('dan_diary.analysis.helper');
        $reports = $em->getRepository('DanPluginDiaryBundle:Report')->findByUser($user);


        foreach($reports as $report) {
            if ($project != $helper->getProject($report)) {
                continue;
            }

            if ($month != $helper->getMonth($report)) {
                continue;
            }

            $date = $helper->getDate($report);
            if (!$date) {
                continue;
            }

            $weekNumber = (int)$date->format('W');
            $dow = (int)$date->format('w');
            $collection->addReport($report, $weekNumber.'.'.$dow);
        }
        
        return array(
            'project' => $project,
            'month' => $month,
            'monthlyReports' => $collection,
        );
    }

    private function getProjectData($user, $project)
    {
        $em = $this->getDoctrine()->getManager();

        $helper = $this->get('dan_diary.analysis.helper');

        $reports = $em->getRepository('DanPluginDiaryBundle:Report')->findByUser($user);

        $totalSeconds = 0;

        $_reports = array();
        foreach($reports as $report) {
            $reportProject = $helper->getProject($report);
            if ($project != $reportProject) {
                continue;
            }

            $month = $helper->getMonth($report);

            $date = $helper->getDate($report);
            if (!$date) {
                continue;
            }

            if (!isset($_reports[$month])) {
                $_reports[$month] = array(
                    'seconds' => 0,
                    'reports' => array(),
                );
            }

            $dailySeconds = $helper->getTotalTime($report);
            
            $weekNumber = (int)$date->format('W');
            $dow = (int)$date->format('w');

            if (!isset($_reports[$month]['reports'][$weekNumber])) {
                $_reports[$month]['reports'][$weekNumber] = array(
                    'seconds' => 0,
                    'reports' => array(),
                );
            }
            if (!isset($_reports[$month]['reports'][$weekNumber]['reports'][$dow])) {
                $_reports[$month]['reports'][$weekNumber]['reports'][$dow] = array(
                    'seconds' => 0,
                    'hours' => '0.00',
                    'date' => $date,
                    'reports' => array(),
                    'tasks' => array(),
                );
            }

            $_reports[$month]['seconds'] += $dailySeconds;

            $totalSeconds += $dailySeconds;

            $_reports[$month]['reports'][$weekNumber]['seconds'] += $dailySeconds;
            $_reports[$month]['reports'][$weekNumber]['reports'][$dow]['seconds'] += $dailySeconds;
            $_reports[$month]['reports'][$weekNumber]['reports'][$dow]['hours'] = $helper->getAsHours($_reports[$month]['reports'][$weekNumber]['reports'][$dow]['seconds']);

            $_reports[$month]['reports'][$weekNumber]['reports'][$dow]['reports'][] = $report;

            $_reports[$month]['reports'][$weekNumber]['reports'][$dow]['tasks'] = array_merge(
                $_reports[$month]['reports'][$weekNumber]['reports'][$dow]['tasks'],
                $this->getAsHtml($helper->getTasks($report))
            );

        }

        $reports = $_reports;

        ksort($reports);
        foreach($reports as $monthKey => $month) {
            ksort($month['reports']);
            $reports[$monthKey] = $month;
            foreach($month['reports'] as $weekNumber => $week) {
                ksort($week['reports']);
                $reports[$monthKey][$weekNumber] = $week;
            }
        }

        $userManager = $this->get('model.manager.user');

        $totalHours = $helper->getAsHours($totalSeconds);
        $euroPerHour = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.euro_per_hour', null);
        $euroPerDay = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.euro_per_day', null);
        $hoursPerDay = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.hours_per_day', null);

        foreach($reports as $monthKey => $month) {
            $month['hours'] = $helper->getAsHours($month['seconds']);            
            $month['days'] = $hoursPerDay ? $month['hours'] / $hoursPerDay : null;
            $month['euros'] = null;

            if ($euroPerDay && $month['days']) {
                $month['euros'] = $month['days'] * $euroPerDay;
            }
            if ($euroPerHour && $month['hours']) {
                $month['euros'] = $month['hours'] * $euroPerHour;
            }
            $reports[$monthKey] = $month;
        }

        $totalHours = $helper->getAsHours($totalSeconds);
        $totalDays = $hoursPerDay ? $totalHours / $hoursPerDay : null;

        $totalEuro = null;
        if ($euroPerDay && $totalDays) {
            $totalEuro = $totalDays * $euroPerDay;
        }
        if ($euroPerHour && $totalHours) {
            $totalEuro = $totalHours * $euroPerHour;
        }


        
        return array(
            'project' => $project,
            'totalHours' => $totalHours,
            'totalSeconds' => $totalSeconds,
            'totalEuro' => $totalEuro,
            'totalDays' => $totalDays,
            'reports' => $reports,
        );
    }

    
}

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
            $date = $helper->getDate($report);
            if (!$date) {
                continue;
            }

            $dailySeconds = $helper->getTotalTime($report);

            
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
                $this->getAsHtml($helper->getTasks($report))
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
        $euroPerDay = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.euro_per_day', null);
        $hoursPerDay = $userManager->getMetadata($user, 'diary', 'settings.projects.'.$project.'.hours_per_day', null);
        $monthlyDays = $hoursPerDay ? $monthlyHours / $hoursPerDay : null;

        $monthlyEuro = null;
        if ($euroPerDay && $monthlyDays) {
            $monthlyEuro = $monthlyDays * $euroPerDay;
        }
        if ($euroPerHour && $monthlyHours) {
            $monthlyEuro = $monthlyHours * $euroPerHour;
        }
        
        return array(
            'project' => $project,
            'month' => $month,
            'monthlyHours' => $monthlyHours,
            'monthlySeconds' => $monthlySeconds,
            'monthlyEuro' => $monthlyEuro,
            'monthlyDays' => $monthlyDays,
            'reports' => $reports,
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

    private function getAsHtml($text)
    {
        if (is_array($text)) {
            foreach($text as $key => $value) {
                $text[$key] = $this->getAsHtml($value);
            }
            return $text;
        }

        $placeholders = array();
        $pattern = '/(?P<ref>http[s]?:\/\/[^\s]+)/';
        while(preg_match($pattern, $text, $matches)) {

            $placeholder = $matches['ref'];
            $placeholders[] = $placeholder; 

            $value = strtr(preg_quote($placeholder), array('/' => '\\/'));
            $text = preg_replace('/'.$value.'/', '{{'.(count($placeholders)-1).'}}', $text, 1);
        }

        $html = $text;
        $pattern = '/{{(?P<i>\d+)}}/';

        while (preg_match($pattern, $html, $matches)) {
            if (!isset($placeholders[(int)$matches['i']])) {
                break;
            }
            $placeholder = $placeholders[(int)$matches['i']];
            $replacement = '<a href="'.$placeholder.'" >'.$placeholder.'</a>';
            $html = preg_replace($pattern, $replacement, $html, 1);
        }

        return $html;
    }

}

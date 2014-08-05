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
 * Widget controller.
 */
class WidgetController extends Controller
{

    /**
     * @Template
     */
    public function selectProjectAction(Request $request, $user, $route, $params, $project)
    {
        $current = $project;

        $em = $this->getDoctrine()->getManager();

        $helper = $this->get('dan_diary.analysis.helper');

        $reports = $em->getRepository('DanPluginDiaryBundle:Report')->findByUser($user);

        $projects = array();
        foreach($reports as $report) {
            if ($project = $helper->getProject($report)) {
                $projects[] = $project;
            }
        }

        $projects = array_unique($projects);
        sort($projects);

        return array(
            'current' => $current,
            'projects' => $projects,
            'user' => $user,
            'route' => $route,
            'params' => $params,
        );
    }


    /**
     * @Template
     */
    public function selectMonthAction(Request $request, $user, $route, $params, $month)
    {
        $current = $month;

        $em = $this->getDoctrine()->getManager();

        $helper = $this->get('dan_diary.analysis.helper');

        $reports = $em->getRepository('DanPluginDiaryBundle:Report')->findByUser($user);

        $yearMonths = array();
        foreach($reports as $report) {
            if (!($date = $helper->getDate($report))) {
                continue;
            }

            $year = $date->format('Y');
            if (!isset($months[$year])) {
                $yearMonths[$year] = array();
            }
            $yearMonths[$year][$date->format('m')] = $date->format('F');
        }

        ksort($yearMonths);
        foreach($yearMonths as $year => $months) {
            ksort($months);
            $yearMonths[$year] = $months;
        }

        return array(
            'current' => $current,
            'yearMonths' => $yearMonths,
            'user' => $user,
            'route' => $route,
            'params' => $params,
        );
    }
}

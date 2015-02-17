<?php

namespace Dan\Plugin\DiaryBundle\Controller;

use Dan\CoreBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Dan\Plugin\DiaryBundle\Entity\Report;

use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="diary_index")
     * @Template()
     */
    public function indexAction()
    {
        $this->givenUserIsLoggedIn();
        $this->checkFirstAccess();

        return array();
    }

    private function checkFirstAccess()
    {
        $user = $this->getUser();
        $userManager = $this->get('model.manager.user');
        $t = $this->get('translator');

        if ($userManager->getMetadata($user, 'diary', 'user_status.example_report_created', false)) {
            return;
        }

        $kernel = $this->get('kernel');
        $content = file_get_contents($kernel->locateResource('@DanPluginDiaryBundle/Resources/data/example_report.it'));

        $date = new \DateTime();
        $today = $t->trans('dow.'.$date->format('D')).' '.$date->format('d').' '.$t->trans('month.'.$date->format('F')).' '.$date->format('Y');
        $date = new \DateTime('+1 day');
        $tomorrow = $t->trans('dow.'.$date->format('D')).' '.$date->format('d').' '.$t->trans('month.'.$date->format('F')).' '.$date->format('Y');
        $content = strtr($content, array(
                '{{today}}' => $today,
                '{{tomorrow}}' => $tomorrow
            ));

        $entity = new Report();
        $entity->setUser($user);
        $entity->setContent($content);

        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush($entity);

        $userManager->setMetadata($user, 'diary', 'settings', $this->getDefaultSettings());

        $userManager->setMetadata($user, 'diary', 'user_status.example_report_created', true);
    }

    private function getDefaultSettings() {
        $kernel = $this->get('kernel');
        $yaml = file_get_contents($kernel->locateResource('@DanPluginDiaryBundle/Resources/data/default_settings.yml'));

        return Yaml::parse($yaml);
    }
}

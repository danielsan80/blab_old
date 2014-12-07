<?php

namespace Dan\Plugin\WwsBundle\Controller;

use Dan\CoreBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    private $stars;
    
    /**
     * @Route("/", name="wws_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
    


    /**
     * @Route("/generate", name="wws_generate")
     * @Template
     */
    public function generateStarsAction()
    {
        $wws = $this->get('wws');
        $stars = $wws->generateStars();
        $wws->saveStars($stars);

        return $this->redirect($this->generateUrl('wws_index'));
    }

    /**
     * @Route("/yml", name="wws_yml")
     * @return yml
     */
    public function getYamlAction()
    {
        $wws = $this->get('wws');
        return new Response($wws->getYaml(), 200, array('Content-Type' => 'application/yml'));
    }

    /**
     * @Route("/labels", name="wws_labels")
     * @Template
     * @return pdf
     */
    public function labelsAction()
    {
        $wws = $this->get('wws');

        return $wws->getLabels();
    }

    /**
     * @Route("/labels/pdf", name="wws_labels_pdf")
     * @return pdf
     */
    public function getLabelsPdfAction()
    {
        $snappy = $this->get('knp_snappy.pdf');
        $snappy->setOption('page-size', 'A4');
        $snappy->setOption('orientation', 'Landscape');
        $route = $this->generateUrl('wws_labels', array(), true);
        return new Response($snappy->getOutput($route), 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @Route("/distances", name="wws_distances")
     * @Template
     * @return pdf
     */
    public function distancesAction()
    {
        $wws = $this->get('wws');

        return $wws->getDistances();;
    }

    /**
     * @Route("/distances/pdf", name="wws_distances_pdf")
     * @return pdf
     */
    public function getDistancesPdfAction()
    {
        $snappy = $this->get('knp_snappy.pdf');
        $snappy->setOption('page-size', 'A4');
        $snappy->setOption('orientation', 'Landscape');

        return new Response($snappy->getOutput($this->generateUrl('wws_distances', array(), true)), 200, array('Content-Type' => 'application/pdf'));
    }


}

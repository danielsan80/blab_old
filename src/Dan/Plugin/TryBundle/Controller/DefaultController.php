<?php

namespace Dan\Plugin\TryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Yaml\Yaml;
use Trt\AsyncTasksBundle\Event\AsyncEvent;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="try_index")
     * @Template()
     */
    public function indexAction()
    {
        $event = new AsyncEvent(
            'async_try',
            array(
                'date'=> (new \DateTime())->format('d-m-Y H:s'),
                'id' => 8
                ,
            )
        );

        $this->get('event_dispatcher')->dispatch($event->getName(), $event);

        return array();
    }
}

<?php

namespace Dan\Plugin\DiaryBundle\Controller;

use Dan\CoreBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="diary_index")
     * @Template()
     */
    public function indexAction()
    {
        $this->givenUserIsLoggedIn();

        return array();
    }
}

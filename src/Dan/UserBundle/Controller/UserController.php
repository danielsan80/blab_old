<?php

namespace Dan\UserBundle\Controller;

use Dan\CommonBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    
    
    /**
     * @Route("/{username}/image", name="user_image"))
     */
    public function imageAction($username)
    {
        if (!($filter = $this->getRequest()->query->get('filter'))) {
            $filter = 'mini';
        }
        $userManager = $this->get('model.manager.user');
        $imageExtension = $this->get('dan_user.twig.image_extension');

        $user = $userManager->findUserBy(array('username' => $username));

        $controller = $this->get('imagine.controller');
        return $controller->filter($imageExtension->user_image($user), $filter);
        
    }
}

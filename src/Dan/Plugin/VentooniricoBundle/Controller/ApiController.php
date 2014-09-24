<?php

namespace Dan\Plugin\VentooniricoBundle\Controller;

use Dan\CoreBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Response;
//use Doctrine\Common\Cache\FilesystemCache;
//use Guzzle\Cache\DoctrineCacheAdapter;
//use Guzzle\Cache\NullCacheAdapter;
//use Guzzle\Plugin\Cache\CachePlugin;
//use Dan\MainBundle\Entity\Game;
//use Dan\Plugin\VentooniricoBundle\Entity\Desire;
use Dan\Plugin\VentooniricoBundle\Service\BGGService;

/**
 * @Route("/api") 
 */
class ApiController extends Controller
{

    /**
     * Request 
     * 
     * @Route("/user", name="user")
     * @Method("GET")
     * 
     * @return json
     */
    public function getUserAction()
    {
        $serializer = $this->get('serializer');

        $user = $this->getUser();
        if ($user) {
            $manager = $this->get('model.manager.user');
            $user = $manager->findUserBy(array('id' => $user->getId()));
        }
        
        $desireManager = $this->get('model.manager.desire');
        
        $desires = $desireManager->getDesiresByOwner($user);
        $user = json_decode($this->serialize($user), true);
        $user['desires_count'] = count($desires);
        $user['desires'] = $desires;
        $user = json_encode($user);
        
        $response = new Response($user, 200, array('Content-Type' => 'application/json'));

        return $response;
    }

    /**
     * Request 
     * 
     * @Route("/games", name="get_games")
     * @Method("GET")
     * 
     * @return json
     */
    public function getGamesAction()
    {
        if ($this->getRequest()->query->get('filter')=='desired') {
            return $this->forward('DanPluginVentooniricoBundle:Api:getDesiredGames');
        }
        $manager = $this->get('model.manager.game');
        $serializer = $this->get('serializer');
        $games = $manager->getAllGames();

        $games = $manager->shiftGames($games);

        $result = $serializer->serialize($games, 'json');

        $response = new Response();
        $response->setContent($result);

        return $response;
    }
    
    /**
     * Request 
     * 
     * @return json
     */
    public function getDesiredGamesAction()
    {
        $manager = $this->get('model.manager.game');
        $serializer = $this->get('serializer');
        $games = $manager->getDesiredGames();

        $result = $serializer->serialize($games, 'json');

        $response = new Response();
        $response->setContent($result);

        return $response;
    }

    /**
     * Request 
     * 
     * @Route("/games/{id}", name="get_game")
     * @Method("GET")
     * 
     * @return json
     */
    public function getGameAction($id)
    {
        $user = $this->getUser();

        $service = new BGGService($this->get('liip_doctrine_cache.ns.bgg'));
        $game = $service->getGame($id);
        $game = $game->getAsArray();

        $em = $this->getDoctrine()->getEntityManager();
        $desireRepo = $em->getRepository('DanPluginVentooniricoBundle:Desire');
        $desires = $desireRepo->findByGameId($id);

        $response = new Response();
        if ($desires) {
            $desire = $desires[0];
            $game['desire'] = $desire->getAsArray();
        }
        $response->setContent(json_encode($game));

        return $response;
    }

    /**
     * Request 
     * 
     * @Route("/desires", name="get_desires")
     * @Method("GET")
     * 
     * @return json
     */
    public function getDesiresAction()
    {
        $manager = $this->get('model.manager.desire');
        $serializer = $this->get('serializer');
        $desires = $manager->getDesires();

        $result = $serializer->serialize($desires, 'json');

        $response = new Response();
        $response->setContent($result);

        return $response;
    }
    
    /**
     * Request 
     * 
     * @Route("/desires", name="post_desire")
     * @Method("POST")
     * 
     * @return json
     */
    public function postDesireAction()
    {

        $user = $this->getUser();

        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        $data = json_decode($request->getContent(), true);
        
        $desire = $this->deserialize('Dan\Plugin\VentooniricoBundle\Entity\Desire', json_encode($data));

        $em->persist($desire);
        $em->flush($desire);

        $response = new Response();
        $response->setContent($this->serialize($desire));
        $response->headers->set('ContentType', 'application/json');

        return $response;
    }

    /**
     * Request 
     * 
     * @Route("/joins", name="post_join")
     * @Method("POST")
     * 
     * @return json
     */
    public function postJoinAction()
    {

        $user = $this->getUser();

        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        $join = $this->deserialize('Dan\Plugin\VentooniricoBundle\Entity\Join', $request->getContent());

        $em->persist($join);
        $em->flush($join);

        $response = new Response();
        $response->setContent($this->serialize($join));
        $response->headers->set('ContentType', 'application/json');

        return $response;
    }

    /**
     * Request 
     * 
     * @Route("/joins/{id}", name="delete_join")
     * @Method("DELETE")
     * 
     * @return json
     */
    public function deleteJoinAction($id)
    {

        $user = $this->getUser();

        $em = $this->getDoctrine()->getEntityManager();
        $joinRepo = $em->getRepository('DanPluginVentooniricoBundle:Join');
        $join = $joinRepo->find($id);
        if ($join) {
            if ($join->getUser()->getId() == $user->getId() || $this->isGranted('ROLE_SUPER_ADMIN')) {
                $em->remove($join);
                $em->flush();
                return new Response('', 200);
            } else {
                return new Response('', 401);
            }
        } else {
            return new Response('', 410);
        }
    }

    /**
     * Request 
     * 
     * @Route("/desires/{id}", name="get_desire")
     * @Method("GET")
     * 
     * @return json
     */
    public function getDesireAction($id)
    {

        $user = $this->getUser();

        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        $desireRepo = $em->getRepository('DanPluginVentooniricoBundle:Desire');
        $desire = $desireRepo->find($id);

        $response = new Response();
        if ($desire) {
            $response->setContent($this->serialize($desire));
        } else {
            $response->setStatusCode('404');
        }

        return $response;
    }

    /**
     * Request 
     * 
     * @Route("/desires/{id}", name="desire_put")
     * @Method("PUT")
     * 
     * @return json
     */
    public function putDesireAction($id)
    {

        $user = $this->getUser();

        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('DanPluginVentooniricoBundle:Desire');
        $desire = $this->deserialize('Dan\Plugin\VentooniricoBundle\Entity\Desire', $request->getContent());
        
        $response = new Response();
        if (!$desire) {
            $this->createNotFoundException();
        }    
        $em->merge($desire);
        $em->flush();
        $desire = $repo->find($id);

        $response->setContent($this->serialize($desire));
        return $response;
    }

    /**
     * Request 
     * 
     * @Route("/desires/{id}", name="delete_desire")
     * @Method("DELETE")
     * 
     * @return json
     */
    public function deleteDesireAction($id)
    {

        $user = $this->getUser();

        $request = $this->getRequest();
        $gameId = $request->get('gameId');
        $em = $this->getDoctrine()->getEntityManager();
        $desireRepo = $em->getRepository('DanPluginVentooniricoBundle:Desire');
        $desire = $desireRepo->findOneById($id);

        $response = new Response();
        if ($desire) {
            if ($desire->getOwner()->getId() == $user->getId() || $this->isGranted('ROLE_SUPER_ADMIN')) {
                $em->remove($desire);
                $em->flush();
                
                $response->setStatusCode(200);
            } else {
                $response->setStatusCode(401);
            }
        } else {
            $response->setStatusCode(410);
        }

        return $response;
    }

}

<?php
namespace Dan\Plugin\DiaryBundle\Model;

use Symfony\Component\HttpFoundation\Request;
use Dan\UserBundle\Entity\User;
use Dan\Plugin\DiaryBundle\Entity\ShareToken;

class ShareTokenManager
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function getUserFromRequest(Request $request)
    {
        if (!($shareToken = $this->getShareTokenFromRequest($request))){
            return null;
        }

        return $shareToken->getUser();
    }

    public function getShareTokenFromRequest(Request $request)
    {
        if (!($token = $request->get('share_token'))){
            return null;
        }

        $shareToken = $this->em->getRepository('DanPluginDiaryBundle:ShareToken')->find($token);

        return $shareToken;
    }

    public function createShareToken(User $user, $route, $params=array())
    {
        $shareToken = new ShareToken();
        $shareToken->setUser($user);
        $shareToken->setRoute($route);
        $shareToken->setParams($params);

        $this->em->persist($shareToken);
        $this->em->flush($shareToken);
    }
    
}
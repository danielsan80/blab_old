<?php
namespace Dan\ShareBundle\Model;

use Symfony\Component\HttpFoundation\Request;
use Dan\UserBundle\Entity\User;
use Dan\ShareBundle\Entity\ShareToken;

class ShareTokenManager
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function getShareTokenById($id)
    {
        $shareToken = $this->em->getRepository('DanShareBundle:ShareToken')->find($id);

        return $shareToken;
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
        if (!($id = $request->get('share_token'))){
            return null;
        }

        $shareToken = $this->getShareTokenById($id);

        return $shareToken;
    }

    public function getShareTokenFromRoute(User $user, $route, $params=array())
    {
        ksort($params);

        $shareTokens = $this->em->getRepository('DanShareBundle:ShareToken')->findBy(array(
                'user' => $user,
                'route' => $route,
            ));

        foreach($shareTokens as $shareToken) {
            $storedParams = $shareToken->getParams();
            ksort($storedParams);
            if ($params==$storedParams) {
                return $shareToken;
            }
        }

        return null;
    }

    public function removeShareToken(ShareToken $shareToken)
    {
        $this->em->remove($shareToken);
        $this->em->flush($shareToken);
    }

    public function resetShareToken(ShareToken $shareToken)
    {
        $newShareToken = $this->createShareToken(
            $shareToken->getUser(),
            $shareToken->getRoute(),
            $shareToken->getParams()
        );
        $this->em->remove($shareToken);
        $this->em->flush($shareToken);

        return $newShareToken;
    }

    public function createShareToken(User $user, $route, $params=array())
    {
        ksort($params);

        $shareToken = new ShareToken();
        $shareToken->setUser($user);
        $shareToken->setRoute($route);
        $shareToken->setParams($params);

        $this->em->persist($shareToken);
        $this->em->flush($shareToken);

        return $shareToken;
    }

    public function createShareData(User $user, $route, $params=array())
    {
        ksort($params);

        $shareData = new ShareData();
        $shareData->setUser($user);
        $shareData->setRoute($route);
        $shareData->setParams($params);

        if ($shareToken = $this->getShareTokenFromRoute($user, $route, $params)) {
            $shareData->setShareToken($shareToken);
        }

        return $shareData;
    }
    
}
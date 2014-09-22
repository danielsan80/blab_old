<?php

namespace Dan\Plugin\VentooniricoBundle\Model;
use Dan\Plugin\VentooniricoBundle\Entity\Game;
use Dan\Plugin\VentooniricoBundle\Service\BGGService;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\Cache;

class GameManager
{
    private $entityName = 'DanPluginVentooniricoBundle:Game';
    private $bgg;
    private $em;
    private $cache;

    public function __construct(EntityManager $em, BGGService $bgg, Cache $cache)
    {
        $this->em = $em;
        $this->bgg = $bgg;
        $this->cache = $cache;
    }
    
    private function getRepository()
    {
        return $this->em->getRepository($this->entityName);
    }

    public function createGame($item=null, $options)
    {
        $game = new Game($item, $options);

        return $game;
    }

    public function getGameByBggId($bggId)
    {
        $this->refreshGames();
        return $this->getRepository()->findByBggId($bggId);
    }
    
    public function getAllGames()
    {
        $this->refreshGames();
        $games = $this->getRepository()->findAll();
        return $games;
    }
    
    public function getDesiredGames()
    {
        $games = $this->getRepository()->findDesired();
        return $games;
    }
    
    private function refreshGames()
    {
        $repo = $this->getRepository();
        $now = new \DateTime();
        if (false === $this->cache->fetch('games')) {
            $games = $this->bgg->getGames();
            foreach($games as $game) {
                $storedGame = $repo->findOneByBggId($game->getBggId());
                if ($storedGame) {
                    $storedGame->setUpdatedAt();
                    if (!($storedGame->isEquals($game))) {
                        $storedGame->merge($game);
                    }
                    $this->em->persist($storedGame);
                } else {
                    $this->em->persist($game);
                }
            }
            $this->em->flush();
            
            $staleGames = $repo->getStaleGames($now);
            foreach($staleGames as $game) {
                $this->em->remove($game);
//                $game->setOwners(array());
            }
            $this->em->flush();
                
            $this->cache->save('games', $games, 3600/4); //TTL 1h
        }
    }

    
    public function shiftGames($games)
    {
        $now = new \DateTime();
        $year = $now->format('Y') - 2010;
        $days = $now->format('z') + ($year * 365);
        $offset = count($games)?$days % count($games):0;
        $offset = count($games) - $offset;
        $slice = array_slice($games, $offset, null, true);
        $games = array_slice($games, 0, $offset, true);
        $games = array_merge($slice, $games);
        return $games;
    }
}

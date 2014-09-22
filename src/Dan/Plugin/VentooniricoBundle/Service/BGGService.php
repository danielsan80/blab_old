<?php

namespace Dan\Plugin\VentooniricoBundle\Service;

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Cache\NullCacheAdapter;
use Guzzle\Plugin\Cache\CachePlugin;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Dan\Plugin\VentooniricoBundle\Entity\Game;

use Dan\MainBundle\Model\MetadataManager;

class BGGService
{

    private $cache;
    private $guzzleClient;
    private $metadataManager;

    public function __construct(MetadataManager $metadataManager = null, Cache $cache = null, GuzzleClient $guzzleClient = null)
    {
        $this->cache = $cache;
        $this->metadataManager = $metadataManager;
        $this->initializeGuzzleClient($guzzleClient, $cache);
    }
    
    private function initializeGuzzleClient($guzzleClient, $cache)
    {
        if (!$guzzleClient) {
            $guzzleClient = new GuzzleClient();
        }

        $guzzleClient->setBaseUrl('http://www.boardgamegeek.com/xmlapi/');

        $adapter = new DoctrineCacheAdapter($cache);
//        $adapter = new NullCacheAdapter();
        $cachePlugin = new CachePlugin(array(
                    'adapter' => $adapter,
                ));

        $guzzleClient->addSubscriber($cachePlugin);

        $this->guzzleClient = $guzzleClient;
    }

    public function getGames()
    {
        $guzzleClient = $this->guzzleClient;
        $cache = $this->cache;
        
        $users = $this->metadataManager->getMetadata('ventoonirico', 'settings.collections', array());

        $requests = array();
        $collections = array();
        foreach ($users as $username => $name) {
            if (false !== ($collection = $cache->fetch('collection.' . $username))) {
                $collections[$username] = $collection;
                continue;
            }
            $request = $guzzleClient->get('collection/' . $username. '?own=1');
            $request->getParams()->set('cache.override_ttl', 300);
            $requests[] = $request;
        }

        $responses = $guzzleClient->send($requests);

        foreach ($responses as $response) {
            $url = (string)$response->getEffectiveUrl();
            preg_match('/(?P<username>\w+)\\?/', $url, $matches);
            $username = $matches['username'];
            $name = $users[$username];
            $xml = $response->xml();
            $games = array();

            $items = $xml->xpath('/items/item');

            foreach ($items as $item) {
                $game = new Game($item, array('user' => $name));
                if ($game->isOwned()) {
                    $games[$game->getBggId()] = $game;
                }
            }
            $collections[$username] = $games;
            $cache->save('collection.' . $username, $games, 3600); //TTL 1h
        }

        $games = array();
        foreach ($collections as $username => $collection) {
            $name = $users[$username];
            foreach ($collection as $bggId => $game) {
                if (isset($games[$bggId])) {
                    $games[$bggId]->addOwner($name);
                } else {
                    $games[$bggId] = $game;
                }
            }
        }
        
        $games = $this->sortGames($games);

        return $games;
    }
    
    public function getGame($id)
    {
        $games = $this->getGames();
        return $games[$id];
    }

    private function sortGames($games)
    {
        $games2 = $games;
        uasort($games2, array($this, 'compare'));
        return $games2;
    }

    private function compare($a, $b)
    {
        $aName = $a->getName();
        $bName = $b->getName();
        if ($aName == $bName) {
            return 0;
        }
        return ($aName < $bName) ? -1 : 1;
    }

}
<?php

namespace Dan\CoreBundle\Test;

use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    
    protected function getFixturesToLoad()
    {
        return array();
    }
    
    public function setUp()
    {
        parent::setUp();
        $executor = $this->loadFixtures($this->getFixturesToLoad());
        $this->referenceRepository = $executor->getReferenceRepository();
        ini_set('memory_limit', '512M');
        set_time_limit ( 600 );

    }
    
    public function tearDown()
    {
        parent::tearDown();
        gc_collect_cycles();
    }
    
    public function getReference($key)
    {
        return $this->referenceRepository->getReference($key);
    }
    
    protected function showInBrowser($client)
    {
        $kernel = $client->getKernel();
        file_put_contents($kernel->getRootDir().'/cache/output.html', $client->getResponse()->getContent());
        exec('firefox '.$kernel->getRootDir().'/cache/output.html');
    }
    
    protected function loginClientWithUser($client, $username, $password, $options = array())
    {
        $options = array_merge(array(
            'login_route' => 'fos_user_security_login',
            'login_button' => 'Login',
        ), $options);
        $crawler = $client->request('GET', $this->getUrl($options['login_route']));
        $form = $crawler->selectButton($options['login_button'])->form(array(
            '_username'  => $username,
            '_password'  => $password,
        ));
        $client->submit($form);
        
        return $client;
    }
    
    protected function assertClientIsOnRoute($client, $route, $parameters = array())
    {
        $this->assertTrue(
                ($this->getUrl($route, $parameters, true) == $client->getRequest()->getUri())
                || (strpos($client->getRequest()->getUri(), $this->getUrl($route, $parameters))!==false)
            ,'The client is not on '. $route . ': '.$this->getUrl($route, $parameters).' != '.$client->getRequest()->getUri()); 
    }
}
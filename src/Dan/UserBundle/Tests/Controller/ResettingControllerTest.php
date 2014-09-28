<?php

namespace Dan\UserBundle\Tests\Controller;

use Dan\CoreBundle\Test\WebTestCase;

class ResettingControllerTest extends WebTestCase
{

    protected function getFixturesToLoad()
    {
        return array(
            'Dan\UserBundle\DataFixtures\ORM\LoadUserData',
            'Dan\UserBundle\DataFixtures\ORM\LoadGroupData',
        );
    }

    public function testResetPassword()
    {
        $client = $this->makeClient();
        $client->enableProfiler();
        
        $url = $this->getUrl('fos_user_security_login', array(), true);
        $crawler = $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET " . $url);
        $crawler = $client->click($crawler->selectLink('dimenticato la password')->link());
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET " . $url);
        
        $form = $crawler->selectButton('ripristina')->form(array(
            'username' => 'aNotExistingUser',
        ));

        $client->submit($form);
        
        $crawler = $client->getCrawler();
        
        $this->assertCount(1, $crawler->filter('.alert-error:contains("resetting.flash.invalid_username")'));
    
        
        
        $form = $crawler->selectButton('ripristina')->form(array(
            'username' => 'mario',
        ));
        
        $client->submit($form);
        
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET " . $url);
        $this->assertClientIsOnRoute($client, 'fos_user_resetting_send_email');
        
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        
        $client->followRedirect();
        
        $crawler = $client->getCrawler();
        $this->assertCount(1, $crawler->filter('p:contains("...@mario.it")'));
        

        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('mario@mario.it', key($message->getTo()));
        
        
        $pattern = '/(?P<url>http[s]?:\/\/[^\s"]+)/';
        preg_match($pattern, $message->getBody(), $matches);
        
        $this->assertArrayHasKey('url', $matches);
        
        $pattern = '/http[s]?:\/\/[^\/]+/';
        $replacement = '';
        $url = preg_replace($pattern,$replacement,$matches['url']);
        
        $crawler = $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET " . $url);
        
        $this->assertCount(1, $crawler->filter('input#fos_user_resetting_form_new'));
        
        $form = $crawler->selectButton('Change password')->form(array(
            'fos_user_resetting_form[new]' => 'newPassword',
        ));
        
        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET " . $url);
        
        $url = $this->getUrl('fos_user_security_login', array(), true);
        $crawler = $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET " . $url);
        
        $this->assertCount(1, $crawler->filter('.alert-fos_user_success:contains("resetting.flash.success")'));
        
        $form = $crawler->selectButton('Login')->form(array(
            '_username' => 'mario',
            '_password' => 'newPassword',
        ));
        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET " . $url);
        $crawler = $client->request('GET', $this->getUrl('home'));
        
        $this->assertClientIsOnRoute($client, 'home');
    }

}

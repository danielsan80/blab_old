<?php

namespace Dan\CoreBundle\Test;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;
use Behat\Mink\Mink;
use Behat\Mink\Selector\CssSelector;
use Behat\Mink\Selector\SelectorsHandler;

use Dan\CoreBundle\Test\Exception\ElementNotFoundException;

class MinkTestCase extends WebTestCase
{
    protected $webDir;
    protected $baseUrl;

    protected function getFixturesToLoad()
    {
        return array();
    }
    
    public function setUp()
    {
        parent::setUp();
        $this->webDir = $this->getContainer()->get('kernel')->getRootDir().'/../web';
        $this->baseUrl = $this->getContainer()->getParameter('base_url');
        ini_set('memory_limit', '512M');
        set_time_limit ( 600 );
    }

    protected function getMinkSession($driver = 'firefox')
    {
        $selector = new CssSelector();
        $handler  = new SelectorsHandler(array(
            'css' => $selector
        ));
        switch ($driver) {
            case 'phantomjs':
            case 'firefox':
                $driver = new Selenium2Driver($driver);
                break;

            case 'symfony':
                $driver = new BrowserKitDriver($this->createClient());
                break;

            default:
                throw new \Exception("I can't configure a session for $driver");
                break;
        }

        $session = new Session($driver, $handler);
        $session->start();
        $session->resizeWindow(1280, 960);

        return $session;
    }

    
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
        return $this;
    }
    
    public function getBaseUrl($default='')
    {
        if ($this->baseUrl) {
            return $this->baseUrl;
        }
        return $default;
    }
    
    public function visit($session, $url)
    {
        $session->visit($this->getBaseUrl().$url);        
    }

    public function getScreenshot($session = null)
    {
        if (!$session) {
            $session = $this->session;
        }
        $content = $session->getScreenshot();
        $now = new \DateTime();
        return $this->saveContent($now->format('Y-m-d_H.i.s').'.png', $content);
    }
    
    public function getPage($session = null)
    {
        if (!$session) {
            $session = $this->session;
        }
        $content = $session->getPage()->getContent();
        $now = new \DateTime();
        return $this->saveContent($now->format('Y-m-d_H.i.s').'.html', $content);
    }
    
    private function saveContent($filename, $content)
    {
        $dir = $this->webDir.'/media/test';
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }
        file_put_contents($dir.'/'.$filename, $content);
        return $this->getBaseUrl().'/media/test/'.$filename;
    }
    
    public function find(Session $session, $selector, $el=null, $mode='css')
    {
        try {
            $el = $this->checkFind($session, $selector, $el, $mode);
        } catch (ElementNotFoundException $e ) {
            $this->throwElementNotFoundException($session, $selector);
        }
        return $el->find($mode, $selector);
    }
    
    public function findAll($session, $selector, $el=null, $mode='css')
    {
        try {
            $el = $this->checkFind($session, $selector, $el, $mode);
        } catch (ElementNotFoundException $e ) {}
        return $el->findAll($mode, $selector);
    }

    public function checkFind($session, $selector, $el=null, $mode='css')
    {
        if (!$el) {
            $el = $session->getPage();
        }
        if (!$el->has($mode, $selector)) {
            throw new ElementNotFoundException('element "'.$selector.'" not found ');
        }
        
        return $el;
    }
    
    public function throwElementNotFoundException($session, $selector=null)
    {
        $url = $this->getScreenshot($session);
        $page = $this->getPage($session);
        if ($selector) {
            $selector = '"'.$selector.'" ';
        }
        throw new ElementNotFoundException('element '.$selector.'not found '. $url.' '. $page);
    }
    
    public function close(Session $session) {
        $session->stop();        
    }
    
    public function waitFor(Session $session, $selector, $timeout=20)
    {
        $session->wait($timeout * 1000, "$('".$selector."').length > 0");
        return $this->find($session, $selector);
    }
    
    public function waitIsVisible($el)
    {
        $time = time();
        while (!$el->isVisible()) {
            if ($time+10<time()) {
                throw new \Exception;('the element wont be visible');
            }
            sleep(1);
        }
        return $el;
    }
}


<?php
namespace Dan\Plugin\DiaryBundle\Parser;

use Dan\MainBundle\Model\ArrayHelper;

abstract class Parser
{
    protected $lexer;
    protected $arrayHelper;
    protected $steps;
    
    protected $executed;
    protected $content;
    protected $tokens;
    protected $properties;
    
    
    public function __construct($content = null)
    {
        $this->steps = array();
        $this->arrayHelper = new ArrayHelper();
        $this->lexer = new Lexer\DefaultLexer();
        
        $this->setContent($content);
    }
    
    private function reset()
    {
        $this->executed = false;
        $this->token = null;
        $this->properties = array();
    }
    
    public function setContent($content)
    {
        $this->reset();
        $this->content = $content;
        
        return $this;
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    public function getTokens()
    {
        return $this->tokens;
    }
    
    public function setProperty($key, $value)
    {
        $this->properties = $this->arrayHelper->setPath($this->properties, $key, $value);
        
        return $this;
    }
    
    public function unsetProperty($key)
    {
        $this->properties = $this->arrayHelper->unsetPath($this->properties, $key);
        
        return $this;
    }
    
    public function getProperty($key, $default = null)
    {
        return $this->arrayHelper->getPath($this->properties, $key, $default);
    }
    
    
    public function setProperties($properties)
    {
        $this->properties = $properties;
        
        return $this;
    }
    
    public function getProperties()
    {
        return $this->properties;
    }
    
    public function explodePropertiesPath($path)
    {
        return $this->arrayHelper->explodePath($this->getProperties(), $path);
    }
    
    public function getParentPath($path)
    {
        return $this->arrayHelper->getParentPath($path);
    }
    
    
    public function addStep(ParserStep $step)
    {
        $step->setParser($this);
        $this->steps[] = $step;
    }
    
    public function execute()
    {
        if ($this->executed) {
            throw new \Exception('Parsing has been executed yet');
        } else {
            $this->executed = true;
        }
        
        $this->setup();
        
        $this->tokens = $this->lexer->run($this->getContent());
        
        foreach($this->steps as $step) {
            $step->execute();
        }
        
        return $this->getProperties();
    }
    
    abstract protected function setup();
    
}
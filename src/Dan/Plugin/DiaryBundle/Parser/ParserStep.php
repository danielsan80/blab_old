<?php
namespace Dan\Plugin\DiaryBundle\Parser;

abstract class ParserStep
{
    protected $parser;
    protected $path;
    
    public function __construct($path = null)
    {
        $this->setPath($path);
    }

    public function setParser(Parser $parser = null)
    {
        $this->parser = $parser;
    }
    
    public function setPath($path)
    {
        $this->path = $path;
    }
    
    public function execute()
    {
        $pathes = $this->parser->explodePropertiesPath($this->path);
        
        foreach($pathes as $path) {
            $this->run($path);
        }
    }
    
    abstract protected function run($path);
}
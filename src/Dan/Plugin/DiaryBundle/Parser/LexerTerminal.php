<?php
namespace Dan\Plugin\DiaryBundle\Parser;

class LexerTerminal implements LexerTerminalInterface
{
    
    private $patterns;
    private $modes;
    private $options;
    private $name;
    
    public function __construct()
    {
        $this->patterns = array();
        $this->options = array();
        $this->modes = array();
    }
    
    public function addPattern($pattern, $mode='')
    {
        $this->patterns[] = $pattern;
        $this->modes[] = $mode;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
            
    public function getName()
    {
        return $this->name;
    }
    
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }
            
    public function getOption($key, $default=null)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        
        return $default;
    }
            
    private function doMatch($string, $atStart = true)
    {
        $caret = $atStart?'^':'';
        foreach($this->patterns as $i => $pattern) {
            $mode = $this->modes[$i];
            $pattern = "/".$caret."(?P<match>".$pattern.")/".$mode;
            if(preg_match($pattern, $string, $matches)) {
                $token = array(
                    'match' => $matches['match'],
                    'token' => $this->getName(),
                );
                $token['matches'] = $matches;
                if (!$atStart) {
                    $token['position'] = strpos($string, $token['match']);
                }
                return $token;
            }
        }
        return false;
    }
    
    public function match($string)
    {
        return $this->doMatch($string, true);
    }
    
    public function findIn($string)
    {
        return $this->doMatch($string, false);
    }
    
    public function isAtStartOfLine()
    {
        return false;
    }
}
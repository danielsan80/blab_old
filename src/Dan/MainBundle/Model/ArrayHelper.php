<?php

namespace Dan\MainBundle\Model;

class ArrayHelper
{
    public function getPath($array, $path, $default = null)
    {
        if (substr($path,0,1)=='.') {
            $path = substr($path,1);
        }
        
        $result = $array;
        if ($path) {
            $path = explode('.', $path);
        } else {
            $path = array();
        }
        
        foreach($path as $i => $part) {
            if (!isset($result[$part])) {
                $result = null;
                break;
            }
            $result = $result[$part];
        }

        if (is_null($result)) {
            $result = $default;
        }

        return $result;
    }
    
    public function setPath($array, $path, $value)
    {
        if (substr($path,0,1)=='.') {
            $path = substr($path,1);
        }
        
        $path = explode('.', $path);
        while(($part = array_shift($path))=='') {
        }

        if (!isset($array[$part])) {
            $array[$part] = array();
        }

        if (count($path)) {
            $array[$part] = $this->setPath($array[$part], implode('.',$path), $value);
        } else {
            $array[$part] = $value;
        }
        
        return $array;
    }
    
    public function unsetPath($array, $path)
    {
        if (substr($path,0,1)=='.') {
            $path = substr($path,1);
        }
        
        $path = explode('.', $path);
        $part = array_shift($path);


        if (count($path) && isset($array[$part])) {
            $array[$part] = $this->unsetPath($array[$part], implode('.',$path));
        } elseif ( isset($array[$part])) {
            unset($array[$part]);
        }
        
        return $array;
    }
    
    public function explodePath($array, $path)
    {
        $path = explode('.', $path);
        $left = array();
        $right = array();
        $pathes = array();
        $i=0;
        for(; $i<count($path); $i++) {
            if ($path[$i]=='*') {
                $i++;
                break;                
            }
            $left[] = $path[$i];
        }
        for(; $i<count($path); $i++) {
            $right[] = $path[$i];
        }
        
        if ($left != $path) {
//            $left = implode('.', $left);
//            $right = implode('.', $right);
            
            $data = $this->getPath($array, implode('.', $left), array());
            
//            if ($right) {
//                $right = '.'.$right;
//            }
            foreach($data as $key => $value) {
                $subpath = array_merge($left, array($key), $right);
                $pathes = array_merge($pathes, $this->explodePath($array, implode('.', $subpath)));
            }
        } else {
            $pathes[] = implode('.', $path);
        }
        
        return $pathes;        
    }
    
    public function getParentPath($path)
    {
        if (!$path) {
            throw new \Exception('root path has not a parent');
        }
        
        $path = explode('.', $path);
        if (count($path)==1) {
            return '';
        }
        array_pop($path);
        
        return implode('.', $path);
    }
    
}
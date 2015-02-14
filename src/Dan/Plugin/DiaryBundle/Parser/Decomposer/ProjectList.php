<?php
namespace Dan\Plugin\DiaryBundle\Parser\Decomposer;

use Dan\Plugin\DiaryBundle\Parser\DecomposerInterface;

class ProjectList implements DecomposerInterface
{
    
    public function decompose($content)
    {
        $patterns = array(
            '/\[(?P<project>\w+)\]/',
        );
        $projects = array();
        $placeholders = array();
        
        while (true) {
            $matches = null;
            foreach($patterns as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    break;
                }                
            }
            if (!$matches) {
                break;
            }
            
            $placeholder = array(
                'value' => $matches['0'],
                'data' => $matches['project'],
                'title' => 'project'
            );
            $placeholders[] = $placeholder; 
            
            $value = strtr(preg_quote($placeholder['value']), array('/' => '\\/'));
            $content = preg_replace('/'.$value.'/', '{{'.(count($placeholders)-1).'}}', $content, 1);
        }
        
        $pattern = '/{{(?P<i>\d+)}}/';
        $projectContents = preg_split($pattern, $content);
        
        foreach($placeholders as $i => $placeholder) {
            $projects[] = array(
                'name' => $placeholder['data'],
                'content' => trim($projectContents[$i+1]),
            );
        }
        
        return array(
            'projects' => $projects,
            'content' => $content,
            'placeholders' => $placeholders,
        );
        
    }
}
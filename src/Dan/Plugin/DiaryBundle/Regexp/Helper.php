<?php
namespace Dan\Plugin\DiaryBundle\Regexp;

use Symfony\Component\Yaml\Yaml;

class Helper
{
    private $transformer;
    private $defaultRegexpFilename;

    public function __construct(Transformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function setDefaultRegexpFilename($filename)
    {
        $this->defaultRegexpFilename = $filename;
    }

    public function getDefaultRegexp() {
        if (!$this->defaultRegexpFilename) {
            return array();
        }
        $yaml = file_get_contents($this->defaultRegexpFilename);

        return Yaml::parse($yaml);
    }

    public function decompose($content, $regexps)
    {
        $properties = array();
        $placeholders = array();
        $data = array();

        if (!$regexps) {
            $regexps = $this->getDefaultRegexp();
        }

        foreach($regexps as $property => $infos) {
            if ($this->isAssociativeArray($infos)){
                $infos = array($infos);
            }
            foreach($infos as $info) {
                $info = array_merge(array(
                        'how_many' => '?',
                        'transformers' => array(),
                        'output' => null,
                    ), $info);

                while(preg_match($info['pattern'], $content, $matches)) {

                    $placeholder = array(
                        'value' => $matches[0],
                        'title' => $property
                    );
                    $placeholders[] = $placeholder; 

                    $content = preg_replace('/'.preg_quote($placeholder['value']).'/', '{{'.(count($placeholders)-1).'}}', $content, 1);


                    foreach($info['transformers'] as $key => $method) {
                        $matches[$key] = $this->transformer->$method($matches[$key]);
                    }

                    $output = $info['output'];
                    if ($output) {
                        while (preg_match('/(%(\w+)%)/',$output, $params )) {
                            $output = strtr($output,array($params[1] => $matches[$params[2]]));
                        }
                    } else {
                        $output = $placeholder['value'];
                    }
                    
                    $properties[$property][] = $output;
                    if ($info['how_many']=='?' || $info['how_many']=='1') {
                        $properties[$property] = $properties[$property][0];
                        break;
                    }
                }
            }
        }


        $data['placeholders'] = $placeholders;
        $data['content'] = $content;
        $data['properties'] = $properties;

        return $data;
    }

    public function getAsHtml($content, $placeholders) {

        $html = $content;
        $pattern = '/{{(?P<i>\d+)}}/';

        while (preg_match($pattern, $html, $matches)) {
            $placeholder = $placeholders[(int)$matches['i']];
            $replacement = '<span class="highlight" title="'.$placeholder['title'].'">'.$placeholder['value'].'</span>';
            $html = preg_replace($pattern, $replacement, $html, 1);
        }

        $html = strtr($html, array("\n" => "<br/>"));

        return $html;
    }

    private function isAssociativeArray($data)
    {
        if (!is_array($data)) {
            return false;
        }

        return array_keys($data) !== range(0, count($data) - 1);
    }

}
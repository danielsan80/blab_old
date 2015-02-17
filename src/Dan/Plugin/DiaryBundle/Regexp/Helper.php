<?php
namespace Dan\Plugin\DiaryBundle\Regexp;

use Symfony\Component\Yaml\Yaml;

class Helper
{

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
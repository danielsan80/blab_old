<?php
namespace Dan\MainBundle\Model;

use Dan\MainBundle\Entity\Metadata;

class MetadataHelper
{

    public function getMetadataContent(MetadataInterface $metadata, $path = null, $default = null, $params = array())
    {
        if (!is_null($default)) {
            if (is_null($metadata->getContent()) && is_null($path)) {
                $metadata->setContent($default);
            }
        }

        if ($path) {
            $result = $metadata->getContent();
            $path = explode('.', $path);
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

            $result = $this->replaceParams($result, $params);

            return $result;
        }

        $result = $metadata->getContent();
        $result = $this->replaceParams($result, $params);
        return $result;
    }

    public function setMetadataContent(MetadataInterface $metadata, $path = null, $content)
    {

        if ($path) {
            $content = $this->appendContent($metadata->getContent(), $content, $path);
        }

        $metadata->setContent($content);
        return $metadata;
    }

    public function appendContent($content, $value, $path)
    {
        if (!$path) {
            return $value;
        }

        $path = explode('.', $path);
        $part = array_shift($path);

        if (!isset($content[$part])) {
            $content[$part] = array();
        }

        $content[$part] = $this->appendContent($content[$part], $value, implode('.',$path));
        return $content;
    }

    public function mergeContent($content, $_content)
    {

        foreach($_content as $key => $value) {
            if (!is_array($value)) {
                $content[$key] = $value;
                continue;
            }
            if (isset($content[$key])) {
                $content = $this->mergeContent($content[$key], $value);
            } else {
                $content[$key] = $value;
            }
        }
        return $content;
    }

    public function replaceParams($data, $params) {
        if (!$params) {
            return $data;
        }
        if (!is_array($data)) {
            return strtr($data, $params);            
        }
        foreach($data as $key => $value) {
            $data[$key] = $this->replaceParams($value, $params);
        }
        return $data;
    }
    
}
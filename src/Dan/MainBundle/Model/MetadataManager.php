<?php
namespace Dan\MainBundle\Model;

use Dan\MainBundle\Entity\Metadata;

class MetadataManager
{
    private $objectManager;

    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getMetadata($context, $path = null, $default = null, $params = array())
    {
        $repo = $this->objectManager->getRepository('DanMainBundle:Metadata');

        $metadata = $repo->findOneBy(array(
            'context' => $context,
        ));

        if (!$metadata) {
            $metadata = new Metadata();
        }

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

    public function setMetadata($context, $path = null, $content)
    {
        $repo = $this->objectManager->getRepository('DanMainBundle:Metadata');

        $metadata = $repo->findOneBy(array(
            'context' => $context,
        ));

        if (!$metadata) {
            $metadata = new Metadata();
            $metadata->setContext($context);
        }

        if ($path) {
            $content = $this->appendContent($metadata->getContent(), $content, $path);
        }

        $metadata->setContent($content);
        $this->objectManager->persist($metadata);
        $this->objectManager->flush($metadata);

    }

    private function appendContent($content, $value, $path)
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

    private function mergeContent($content, $_content)
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

    private function replaceParams($data, $params) {
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
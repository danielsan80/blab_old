<?php
namespace Dan\Plugin\DiaryBundle\Model\Manager;

use Dan\Plugin\DiaryBundle\Parser\Parser\DefaultParser;
use Dan\Plugin\DiaryBundle\Regexp\Helper as RegexpHelper;
use Dan\Plugin\DiaryBundle\Model\ReportChild;
use Symfony\Component\Yaml\Yaml;

use Doctrine\ORM\EntityManager;

use Dan\MainBundle\Model\ArrayHelper;

class ReportManager
{
    const ENTITY_NAME = 'DanPluginDiaryBundle:Report';
    private $em;
    private $regexpHelper;
    
    public function __construct(EntityManager $em, RegexpHelper $regexpHelper)
    {
        $this->em = $em;
        $this->regexpHelper = $regexpHelper;
    }
    
    public function parseContent($content)
    {
        $parser = new DefaultParser($content);
        $parser->execute();

        return $parser->getProperties();
    }
    
    public function parseContentForPreview($content)
    {
        $arrayHelper = new ArrayHelper();
        
        $properties = $this->parseContent($content);
        $pathes = $arrayHelper->explodePath($properties, 'properties.dates.*.projects.*.content');
        foreach($pathes as $path) {
            $properties = $arrayHelper->unsetPath($properties, $path);
        }
        $data = array();
        $data['html'] = $this->regexpHelper->getAsHtml($properties['content'], $properties['placeholders']);
        
        $data['properties_yaml'] = strtr(Yaml::dump($properties['properties'], 100), array('    ' => '  '));
        
        return $data;
    }
    
    public function getReportsByUser($user)
    {
        return $this->em->getRepository(self::ENTITY_NAME)->findByUser($user);
    }
    
    public function explodeReports($reports)
    {
        $children = array();
        foreach($reports as $report) {
            $children = array_merge($children, $this->explodeReport($report));
        }
        
        return $children;
    }
    
    public function explodeReport($report)
    {
        $properties = $report->getProperties();
        $arrayHelper = new ArrayHelper();
        $children = array();
        
        $datePathes = $arrayHelper->explodePath($properties, 'dates.*');
        foreach($datePathes as $datePath) {
            $date = $arrayHelper->getPath($properties, $datePath.'.date');
            $projectPathes = $arrayHelper->explodePath($properties, $datePath.'.projects.*');
            foreach($projectPathes as $projectPath) {
                $data = $arrayHelper->getPath($properties, $projectPath);
                $data['date'] = $date;
                $child = new ReportChild();
                $child->setContent($arrayHelper->getPath($data, 'content', ''));
                $child->setProperties($data);
                $child->setParent($report);
                
                $children[] = $child;
            }
        }
        
        return $children;
    }
}
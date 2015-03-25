<?php

namespace Dan\Plugin\WwsBundle\Service;

use Symfony\Component\Yaml\Yaml;

class WWS
{

    const N_STARS = 50;
    const R_SPHERE = 20;
    const MIN_DISTANCE = 2;
    const MAX_DISTANCE = 10;

    private $options;
    private $storeFilename;
    private $starGenerator;

    public function __construct()
    {
        $this->setOptions();
        $this->starsGenerator = new StarsGenerator();
    }

    public function getDefaultOptions()
    {
        return array(
            'board_width' => 297,
            'board_height' => 210,
            'board_padding' => 5,
            'box_size' => 20,
            'box_margin' => 5,
            'plan_inclination' => 4,
        );
    }

    public function setOptions($options = array())
    {
        if (!is_array($options)) {
            throw new \Exception('$options must be an array');
        }

        $options = array_merge($this->getDefaultOptions(), $options);
        $this->options = $options;
    }

    public function setStoreFilename($filename)
    {
        $this->storeFilename = $filename;
    }

    public function issetStoreFilename()
    {
        return (bool) $this->storeFilename;
    }

    public function existsStoreFilename()
    {
        return file_exists($this->storeFilename);
    }
    
    public function generateStars()
    {
        $this->starsGenerator->generateStars();

        return $this->starsGenerator->getStars();
    }

    public function saveStars($stars)
    {
        passthru("mkdir -p ".dirname($this->storeFilename));
        file_put_contents($this->storeFilename, Yaml::dump($stars, 9));
    }

    public function getYaml()
    {
        return file_get_contents($this->storeFilename);
    }

    public function getStars()
    {
        $stars = Yaml::parse(file_get_contents($this->storeFilename));
        return $stars;
    }

    public function getLabels()
    {
        $options = $this->options;

        $stars = $this->getStars();



        $i = 0;
        $boxes = array();
        foreach ($stars as $id => $star) {
            $box = array();
            $box['idMode'] = $star['coord']['alt'] < pi() / 2 ? 'top' : 'bottom';
            $box['id'] = $id;
            $box['pos'] = $this->getPos($i);
            $box = array_merge($box, $this->getAziAlt($star['coord']));
            $box['coord'] = $star['coord'];
            $boxes[$i] = $box;
            $i++;
        }


        $data = array(
            'board' => array(
                'width' => $options['board_width'],
                'height' => $options['board_height'],
            ),
            'box' => array(
                'size' => $options['box_size'],
            ),
            'boxes' => $boxes,
        );
        return $data;
    }

    public function getDistances()
    {
        $options = $this->options;

        $stars = $this->getStars();

        $i = 0;
        $headRows = 5;
        $rowsPerCol = 63;
        $cols = array();
        $col = array();
        foreach ($stars as $key => $star) {
            if ($i + $headRows > $rowsPerCol) {
                $cols[] = $col;
                $col = array();
                $i = 0;
            }
            $col[] = array('type' => 'head', 'key' => $key, 'coord' => $star['coord']);
            $i += $headRows;

            foreach ($star['distances'] as $key => $distance) {
                if ($i+1 > $rowsPerCol) {
                    $cols[] = $col;
                    $col = array();
                    $i = 0;
                }
                $col[] = array('type' => 'distance', 'key' => $key, 'distance' => $distance);
                $i++;
                
            }
            $col[] = array('type' => 'separator');
        }

        $data = array(
            'board' => array(
                'width' => $options['board_width'],
                'height' => $options['board_height'],
            ),
            'cols' => $cols,
        );
        return $data;
    }


    private function addXYZ($coord)
    {
        $r = $coord['r'];
        $teta = $coord['alt'];
        $fi = $coord['azi'];

        $coord['y'] = $r * sin($teta) * cos($fi);
        $coord['x'] = $r * sin($teta) * sin($fi);
        $coord['z'] = -$r * cos($teta);

        return $coord;
    }

    private function getPos($i)
    {
        $options = $this->options;

        $nCol = floor(($options['board_width'] - (2 * $options['board_padding']) + $options['box_margin'] ) / ($options['box_size'] + $options['box_margin']));

        $col = $i % $nCol;
        $row = floor($i / $nCol);

        $x = $options['board_padding'] + ($col * ($options['box_size'] + $options['box_margin']));
        $y = $options['board_padding'] + ($row * ($options['box_size'] + $options['box_margin']));
        return array(
            'x' => $x,
            'y' => $y,
        );
    }

    private function getAziAlt($coord)
    {
        $coord = $this->addXYZ($coord);

        $rate = 1 / $this->options['plan_inclination'];
        $boxSize = $this->options['box_size'];


        $x = (($coord['x'] + 1) / 2) * $boxSize;
        $y = (($coord['y'] + 1) / 2) * $boxSize;
        $y = $y * $rate;
        $y += ($boxSize - ($boxSize * $rate)) / 2;

        $x0 = $boxSize / 2;
        $y0 = $boxSize / 2;

        $r = sqrt(pow($x - $x0, 2) + pow($y - $y0, 2));
        $teta = acos(($y - $y0) / $r);
        if ($coord['azi'] > pi()) {
            $teta = 2 * pi() - $teta;
        }

        $azi = array();
        $azi['r'] = $r;
        $azi['rotate'] = $teta;
        $azi['x'] = ($boxSize / 2) + ($r * cos(pi() / 2 - $teta) / 2);
        $azi['y'] = ($boxSize / 2) - ($r / 2) - ($r / 2 * sin(pi() / 2 - $teta));

        $height = abs($coord['z'] * ($boxSize / 2));
        $up = $coord['z'] >= 0;

        $alt = array();
        $alt['height'] = $height;
        $alt['x'] = $x;
        $alt['y'] = $boxSize - $y;


        if ($up) {
            $alt['y'] -= $alt['height'];
        }
        $star = array();
        $star['x'] = $alt['x'];
        $star['y'] = $alt['y'];

        if (!$up) {
            $star['y'] += $alt['height'];
        }

        return array(
            'azi' => $azi,
            'alt' => $alt,
            'star' => $star
        );
    }

}
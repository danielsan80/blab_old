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

    public function __construct()
    {
        $this->options = $this->getDefaultOptions();
    }

    public function getDefaultOptions()
    {
        return array(
            'num_stars' => 50,
            'sphere_r' => 20,
            'min_distance' => 2,
            'max_distance' => 10,
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
        $n = $this->options['num_stars'];
        $r = $this->options['sphere_r'];
        $min = $this->options['min_distance'];
        $max = $this->options['max_distance'];

        $stars = array();
        for ($i = 0; $i < $n; $i++) {
            $stars[$i] = array(
                'coord' => array(
                    'r' => $this->rand(),
                    'azi' => $this->rand() * 2 * pi(),
                    'alt' => ($this->rand() * pi()),
                ),
            );
        }

        foreach ($stars as $i => $star1) {
            foreach ($stars as $j => $star2) {
                if ($i != $j) {
                    $distance = round($this->distance($star1, $star2) * $r);
                    if ($distance < $min) {
                        unset($stars[$j]);
                        continue;
                    }
                }
            }
        }
        $stars = array_values($stars);

        $_stars = array();
        foreach ($stars as $i => $star) {
            $_stars[str_pad($i, 3, '0', STR_PAD_LEFT)] = $star;
        }
        $stars = $_stars;

        foreach ($stars as $i => $star1) {
            $distances = array();
            foreach ($stars as $j => $star2) {
                if ($i != $j) {
                    $distance = round($this->distance($star1, $star2) * $r);
                    if ($distance <= $max) {
                        $distances[$j] = $distance;
                    }
                }
            }
            asort($distances);
            $stars[$i]['distances'] = $distances;
        }

        foreach ($stars as $i => $star) {
            $stars[$i]['coord']['r'] = round($stars[$i]['coord']['r'], 2);
            $stars[$i]['coord']['alt'] = round($stars[$i]['coord']['alt'], 2);
            $stars[$i]['coord']['azi'] = round($stars[$i]['coord']['azi'], 2);
        }

        return $stars;
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

    private function rand()
    {
        $precision = 1000;
        return rand(0, $precision) / $precision;
    }

    private function distance($star1, $star2)
    {
        $r1 = $star1['coord']['r'];
        $teta1 = $star1['coord']['alt'];
        $fi1 = $star1['coord']['azi'];

        $r2 = $star2['coord']['r'];
        $teta2 = $star2['coord']['alt'];
        $fi2 = $star2['coord']['azi'];

        $x = pow($r1 * sin($teta1) * cos($fi1) - $r2 * sin($teta2) * cos($fi2), 2);
        $y = pow($r1 * sin($teta1) * sin($fi1) - $r2 * sin($teta2) * sin($fi2), 2);
        $z = pow($r1 * cos($teta1) - $r2 * cos($teta2), 2);

        return sqrt($x + $y + $z);
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
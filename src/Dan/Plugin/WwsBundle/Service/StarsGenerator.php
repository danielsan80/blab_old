<?php

namespace Dan\Plugin\WwsBundle\Service;

use Symfony\Component\Yaml\Yaml;

class StarsGenerator
{

    const N_STARS = 50;
    const R_SPHERE = 20;
    const MIN_DISTANCE = 2;
    const MAX_DISTANCE = 10;

    private $options;
    private $stars;

    public function __construct()
    {
        $this->options = $this->getDefaultOptions();
        $this->stars = array();
    }

    public function getDefaultOptions()
    {
        return array(
            'num_stars' => 50,
            'sphere_r' => 20,
            'min_distance' => 2,
            'max_distance' => 10,
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

    private function addStars($n)
    {
        for ($i = 0; $i < $n; $i++) {
            $this->stars[] = array(
                'coord' => array(
                    'r' => $this->rand(),
                    'azi' => $this->rand() * 2 * pi(),
                    'alt' => ($this->rand() * pi()),
                ),
            );
        }
        
        $this->stars = array_values($this->stars);
    }
    
    private function removeTooCloseStars()
    {
        $r = $this->options['sphere_r'];
        $min = $this->options['min_distance'];
        
        while(true) {
            $found = false;
            foreach ($this->stars as $i => $star1) {
                foreach ($this->stars as $j => $star2) {
                    if ($i < $j) {
                        $distance = round($this->distance($star1, $star2) * $r);
                        if ($distance < $min) {
                            $found = true;
                            unset($this->stars[$j]);
                            break 2;
                        }
                    }
                }
            }
            if (!$found) {
                break;
            }
        }
        
        $this->stars = array_values($this->stars);
        
    }
    
    private function removeOneTooFarStar()
    {
        $r = $this->options['sphere_r'];
        $max = $this->options['max_distance'];
        
        $tooFarStarDistance = 0;
        $tooFarStar = null;
        
        foreach ($this->stars as $i => $star1) {
            $minDistance = 2 * $r;
            foreach ($this->stars as $j => $star2) {
                if ($i != $j) {
                    $distance = round($this->distance($star1, $star2) * $r);

                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                    }
                }
            }
            $this->stars[$i]['distance_to_closest'] = $minDistance;
            
            
            if ( $minDistance > $tooFarStarDistance ) {
                $tooFarStar = $i;
                $tooFarStarDistance = $minDistance;
            }
        }
        if ($tooFarStarDistance > $max) {
            unset($this->stars[$tooFarStar]);
        }

        $this->stars = array_values($this->stars);
    }
    
    
    private function renameStarsKeys()
    {
        $_stars = array();
        foreach ($this->stars as $i => $star) {
            $_stars[str_pad($i, 3, '0', STR_PAD_LEFT)] = $star;
        }
        $this->stars = $_stars;
    }
    
    private function calculateStarsDistances()
    {
        $r = $this->options['sphere_r'];
        foreach ($this->stars as $i => $star1) {
            $distances = array();
            foreach ($this->stars as $j => $star2) {
                if ($i != $j) {
                    $distance = round($this->distance($star1, $star2) * $r);
                    $distances[$j] = $distance;
                }
            }
            asort($distances);
            $this->stars[$i]['distances'] = $distances;
        }
    }
    
    private function roundStarsCoordinates()
    {
        foreach ($this->stars as $i => $star) {
            $this->stars[$i]['coord']['r'] = round($this->stars[$i]['coord']['r'], 2);
            $this->stars[$i]['coord']['alt'] = round($this->stars[$i]['coord']['alt'], 2);
            $this->stars[$i]['coord']['azi'] = round($this->stars[$i]['coord']['azi'], 2);
        }
    }
    
    private function clearStarsData()
    {
        foreach ($this->stars as $i => $star) {
            unset($this->stars[$i]['distance_to_closest']);
        }
    }
            

    public function generateStars()
    {
        $n = $this->options['num_stars'];
        $r = $this->options['sphere_r'];
        $min = $this->options['min_distance'];
        $max = $this->options['max_distance'];

        $i = 0;
        while (count($this->stars)<$n) {
            if ($i++>100) {
                break;
            }
            $this->addStars($n-count($this->stars));
            $this->removeTooCloseStars();
            $this->removeOneTooFarStar();
        }
        
        $this->renameStarsKeys();

        $this->calculateStarsDistances();
        $this->roundStarsCoordinates();
        $this->clearStarsData();

        return $this->stars;
    }

    public function getStars()
    {
        return $this->stars;
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

}
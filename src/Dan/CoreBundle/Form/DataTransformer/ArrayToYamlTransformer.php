<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dan\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Yaml\Yaml;

class ArrayToYamlTransformer implements DataTransformerInterface
{
    

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($yaml)
    {
        return Yaml::parse($yaml);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($array)
    {
        return Yaml::dump($array, 9);
    }
}

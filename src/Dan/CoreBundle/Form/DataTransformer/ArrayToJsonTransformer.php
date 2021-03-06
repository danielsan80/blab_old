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

class ArrayToJsonTransformer implements DataTransformerInterface
{

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($json)
    {
        return json_decode($json, true);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($array)
    {
        return json_encode($array);
    }
}

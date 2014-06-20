<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Dan\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Dan\CoreBundle\Form\DataTransformer\ArrayToJsonTransformer;

class JsonArrayType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ArrayToJsonTransformer();
        $builder->addViewTransformer($transformer);
    }

    public function getParent()
    {
        return 'textarea';
    }
    
    public function getName()
    {
        return 'json_array';
    }
}

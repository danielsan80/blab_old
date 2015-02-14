<?php

namespace Dan\MainBundle\Tests\Model;

use Dan\CoreBundle\Test\WebTestCase;
use Dan\MainBundle\Model\ArrayHelper;

class ArrayHelperTest extends WebTestCase
{

    public function test_getWhenPathDoNotExist()
    {
        $helper = new ArrayHelper();
        $data = array();

        $this->assertNull($helper->getPath($data, 'a.path'));

        $this->assertEquals('aDefaultValue', $helper->getPath($data,'a.path', 'aDefaultValue'));
    }

    public function test_getWhenPathExists()
    {
        $helper = new ArrayHelper();
        $data = [
            'an' => [
                'existing' => [
                    'path' => 'value',
                ],
            ],
        ];
        
        $this->assertEquals($data, $helper->getPath($data, ''));
        $this->assertEquals($data, $helper->getPath($data, null));

        $this->assertEquals('value', $helper->getPath($data, 'an.existing.path'));
        $this->assertEquals('value', $helper->getPath($data, 'an.existing.path', 'aDefaultValue'));

        $this->assertEquals($data['an']['existing'], $helper->getPath($data, 'an.existing'));
        $this->assertEquals($data['an'], $helper->getPath($data, 'an'));
    }
    
    public function test_set()
    {
        $helper = new ArrayHelper();
        $data = [
            'an' => [
                'existing' => [
                    'path' => 'value',
                ],
            ],
        ];

        $result = $helper->setPath($data, 'an.existing.path', 'anOtherValue');
        $this->assertEquals('value', $data['an']['existing']['path']);
        $this->assertEquals('anOtherValue', $result['an']['existing']['path']);
        
        $result = $helper->setPath($data, 'an.existing.path2', 'anOtherValue');
        $this->assertFalse(isset($data['an']['existing']['path2']));
        $this->assertEquals('value', $result['an']['existing']['path']);
        $this->assertEquals('anOtherValue', $result['an']['existing']['path2']);
        
        $result = $helper->setPath($data, 'an.existing', ['path2' => 'value2']);
        $this->assertFalse(isset($result['an']['existing']['path']));
        $this->assertEquals('value2', $result['an']['existing']['path2']);

    }
    
    public function test_reset()
    {
        $helper = new ArrayHelper();
        $data = [
            'an' => [
                'existing' => [
                    'path' => 'value',
                ],
            ],
        ];

        $result = $helper->unsetPath($data, 'an.existing.path');
        $this->assertEquals('value', $data['an']['existing']['path']);
        $this->assertFalse(isset($result['an']['existing']['path']));
        $this->assertEquals([], $result['an']['existing']);

        $result = $helper->unsetPath($data, 'an');
        $this->assertEquals('value', $data['an']['existing']['path']);
        $this->assertEquals([], $result);
        
        $result = $helper->unsetPath($data, 'a.not.existing.path');
        $this->assertEquals('value', $data['an']['existing']['path']);
        
        $result = $helper->unsetPath($data, 'an.existing.notExistingPath');
        $this->assertEquals($data, $result);
        
    }
    
    public function test_explodePath()
    {
        $helper = new ArrayHelper();
        $data = [
            'elements' => [
                'H',
                'O',
                'C',
                'He',
            ]
        ];

        $pathes = [
            'elements.0',
            'elements.1',
            'elements.2',
            'elements.3',
        ];
        
        $this->assertEquals($pathes, $helper->explodePath($data, 'elements.*'));
        
        $this->assertEquals(['elements'], $helper->explodePath($data, 'elements'));
        
    }
    
    public function test_explodePathMore()
    {
        $helper = new ArrayHelper();
        $data = [
            'elements' => [
                'H' => [ 
                    'molecules' => [
                        'H20',
                        'H2',
                        'CH4',
                    ],
                ],
                'O' => [
                    'molecules' => [
                        'O2',
                        'O3',
                        'H2O',
                        'CO2',
                    ],
                ],
                'C' => [
                    'molecules' => [
                        'CH4',
                        'CO2',                    
                    ],
                ],
                'He' => [],
            ],
        ];

        $pathes = [
            'elements.H.molecules.0',
            'elements.H.molecules.1',
            'elements.H.molecules.2',
            
            'elements.O.molecules.0',
            'elements.O.molecules.1',
            'elements.O.molecules.2',
            'elements.O.molecules.3',
            
            'elements.C.molecules.0',
            'elements.C.molecules.1',
        ];
        
        $this->assertEquals($pathes, $helper->explodePath($data, 'elements.*.molecules.*'));
        $this->assertEquals(array('elements'), $helper->explodePath($data, '*'));
    }
    
}

<?php

namespace Dan\UserBundle\Twig;

class ImageExtension extends \Twig_Extension
{

    public function getFilters()
    {
        return array(
            'user_image' => new \Twig_Filter_Method($this, 'user_image'),
        );
    }

    public function getFunctions()
    {
        return array(
            'user_image' => new \Twig_Function_Method($this, 'user_image'),
        );
    }
    public function getName()
    {
        return 'image_extension';
    }

    public function user_image($user)
    {
        if (!$user->getImage()){
            return 'icons/user-noimage.png';
        }
        
        return 'users/'.$user->getImage();
    }
}
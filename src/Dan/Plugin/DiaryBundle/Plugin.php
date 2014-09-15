<?php
namespace Dan\Plugin\DiaryBundle;

use Dan\MainBundle\Plugin\AbstractPlugin;

class Plugin extends AbstractPlugin
{

    public function getCode()
    {
        return 'diary';
    }
    
    public function getName()
    {
        return 'Diary';
    }

    public function getBundleName()
    {
        return 'DanPluginDiaryBundle';
    }

    public function getRootRoute()
    {
        return 'diary_index';
    }

}
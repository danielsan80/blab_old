<?php

namespace Dan\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class InstallPluginsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dan:plugins:install')
            ->setDescription('Install plugin less for all plugins')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $pluginManager = $container->get('dan.plugin_manager');
        $plugins = $pluginManager->getPlugins();

        $kernel = $container->get('kernel');

        $mainLessDir = $kernel->locateResource('@DanMainBundle/Resources/public/less');

        if ($dh = opendir($mainLessDir.'/plugin')) {
            while (($file = readdir($dh)) !== false) {
                if (substr($file,0,1) == '.') {
                    continue;
                }
                passthru('rm -Rf '.$mainLessDir.'/plugin/'.$file);
            }
            closedir($dh);
        }

        $content='';
        file_put_contents($mainLessDir.'/plugin.less', $content);
        foreach($plugins as $plugin) {
            try {
                $pluginLessDir = $kernel->locateResource('@'.$plugin->getBundleName().'/Resources/public/less');
            } catch (\Exception $e) {
                continue;
            }

            if (!file_exists($pluginLessDir.'/index.less')) {
                continue;
            }

            passthru('ln -s '.$pluginLessDir.' '.$mainLessDir.'/plugin/'.$plugin->getCode());
            $content .= '@import "plugin/'.$plugin->getCode().'/index.less";'."\n";
        }
        file_put_contents($mainLessDir.'/plugin.less', $content);

        $output->writeln('DONE');
    }
}

<?php

namespace Dan\Plugin\TryBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateSupervisorScriptsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dan_try:supervisor_scripts:generate')
            ->setDescription('Generate the bash script to ensure that the asinc command is running')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $queueList = $container->get('dan_try.queue_list');
        $queues = $queueList->getQueues();

        $templating = $container->get('templating');
        $kernel = $container->get('kernel');
        $console = $kernel->getRootDir().'/console';

        $scriptDir = $kernel->locateResource('@DanPluginTryBundle/Resources/scripts/supervisor');

        if ($dh = opendir($scriptDir)) {
            while (($file = readdir($dh)) !== false) {
                if (substr($file,0,1) == '.') {
                    continue;
                }
                unlink($scriptDir.'/'.$file);
            }
            closedir($dh);
        }

        foreach($queues as $queue) {
            $script = $templating->render(
                'DanPluginTryBundle::supervisor.sh.twig',
                array(
                    'command' => 'trt:async:run ' . $queue,
                    'console' => $console,
                )
            );
            file_put_contents($scriptDir.'/'.$queue.'.sh', $script);
            chmod($scriptDir.'/'.$queue.'.sh', 777);
        }
    }
}

<?php
use Idephix\Idephix;
use Idephix\Extension\Deploy\Deploy;
use Idephix\Extension\PHPUnit\PHPUnit;

$localBaseDir = __DIR__;
$sshParams = array(
    'user' => 'root',
);

$targets = array(
    'prod' => array(
        'hosts' => array('pp'),
        'ssh_params' => $sshParams,
        'deploy' => array(
            'local_base_dir' => $localBaseDir,
            'remote_base_dir' => "/var/www/blab/",
            'rsync_exclude_file' => 'rsync_exclude.txt',
            'shared_folders' => array(
                'app/config',
                'app/logs',
                'app/sessions',
                'app/spool',
                'app/files/images/users',
                'app/files/uploads/media',
                'web/media'
            ),
            'shared_symlinks' => array(
                'app/config/parameters.yml',
                'app/logs',
//                'app/sessions',
//                'app/spool',
//                'app/files/images/users',
//                'app/files/uploads/media',
//                'web/media'
            )
            // 'rsync_include_file' => 'rsync_include.txt'
            // 'migrations' => true
            // 'strategy' => 'Copy'
        ),
    ),
);

$idx = new Idephix($targets);

$idx->
    /**
     * Symfony2 basic deploy
     */
    add('deploy',
        function($go = false) use ($idx)
        {
            if (!$go) {
                echo "\nDry Run...\n";
            }
            $idx->deploySF2Copy($go);
        })->
    /**
     * Build your Symfony project after you have downloaded it for the first time
     */
    add('build:fromscratch',
        function () use ($idx)
        {
            if (!file_exists(__DIR__.'/composer.phar')) {
                $idx->output->writeln("Downloading composer.phar ...");
                shell_exec('curl -sS https://getcomposer.org/installer | php');
            }

            passthru("php composer.phar update");
            passthru("./app/console doctrine:schema:update --force");
            $idx->runTask('asset:install');
            passthru("./app/console cache:clear --env=dev");
            passthru("./app/console cache:clear --env=test");
            //$idx->runTask('test:run');
        })->
    /**
     * Symfony2 installing assets and running assetic command
     */
    add('asset:install',
        function () use ($idx)
        {
            passthru("app/console assets:install web");
            passthru("app/console assetic:dump");
        })->
    /**
     * run phpunit tests
     */
    add('test:run',
        function () use ($idx)
        {
            $idx->runPhpUnit('-c app/');
        })
    ;

$idx->addLibrary('deploy', new Deploy());
$idx->addLibrary('phpunit', new PHPUnit());

$idx->run();
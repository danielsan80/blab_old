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
    add('deploy',
        function($go = false) use ($idx)
        {
            if (!$go) {
                echo "\nDry Run...\n";
            }
            $idx->deploySF2Copy($go);
        })->
                
    add('cc',
        function() use ($idx)
        {
             $idx->local('rm -Rf app/cache/*');
        })->
    
    add('chmod',
        function() use ($idx)
        {
            $idx->local("chmod -R 777 app/cache app/logs  app/files app/sessions web/media");
            $idx->local("setfacl -Rn -m u:www-data:rwX -m u:`whoami`:rwX app/cache app/logs  app/files app/sessions web/media");
            $idx->local("setfacl -dRn -m u:www-data:rwX -m u:`whoami`:rwX app/cache app/logs  app/files app/sessions web/media");
        })->
    add('asset:install',
        function () use ($idx)
        {
            $idx->local("app/console assets:install");
            $idx->local("app/console assetic:dump");
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
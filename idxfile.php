<?php
use Idephix\Idephix;
use Idephix\Extension\Deploy\Deploy;
use Idephix\Extension\PHPUnit\PHPUnit;
use Symfony\Component\Yaml\Yaml;

$parameters = Yaml::parse(file_get_contents('idx/parameters.yml'));

if (!$parameters) {
    $parameters = array();
}
if (!isset($parameters['targets'])) {
    $parameters['targets'] = array();
}

$localBaseDir = __DIR__;

$targets = array_merge_recursive($parameters['targets'], array(
    'prod' => array(
        'hosts' => array('ocean'),
        'ssh_params' => array(
            'user' => 'root',
        ),
        'deploy' => array(
            'local_base_dir' => $localBaseDir,
            'remote_base_dir' => "/var/www/blab",
            // 'rsync_include_file' => 'rsync_include.txt'
            'rsync_exclude_file' => 'idx/rsync_exclude',
            'shared_folders' => array(
                'app/config',
                'app/logs',
                'app/sessions',
                'app/spool',
                'app/files/images/users',
                'web/media/cache'
            ),
            'shared_symlinks' => array(
                'app/config/parameters.yml',
                'app/logs',
                'app/sessions',
                'app/spool',
                'app/files/images/users',
                'web/media'
            ),
            'migrations' => true,
            'assetic' => false
            // 'strategy' => 'Copy'
        ),
    ),
));

$idx = new Idephix($targets);

$idx->
    add('build',
        function() use ($idx)
        {
            try{
                $idx->local('app/console doctrine:database:create');
            } catch (\Exception $e) {}
            $idx->runTask('dbreset');
            $idx->local('app/console dan:plugins:install');
            $idx->runTask('assets:install');
        })->

    add('deploy',
        function($go = false) use ($idx)
        {
            if (!$go) {
                echo "\nDry Run...\n";
            }
            if ($go) {
                $idx->runTask('chmod:remote');
            }
            $idx->deploySF2Copy($go);
        })->
                
    add('dbreset',
        function() use ($idx)
        {
            try{
                $idx->local('app/console doctrine:database:create');
            } catch (\Exception $e) {}
             $idx->local('app/console doctrine:database:drop --force');
             $idx->local('app/console doctrine:database:create');
             $idx->local('app/console doctrine:migrations:migrate --no-interaction');
             $idx->local('app/console doctrine:fixtures:load --no-interaction');
             $idx->runTask('cc');
        })->

    add('dbreset-test',
        function() use ($idx)
        {
            try{
                $idx->local('app/console doctrine:database:create --env=test');
            } catch (\Exception $e) {}
             $idx->local('app/console doctrine:database:drop --force --env=test');
             $idx->local('app/console doctrine:database:create --env=test');
             $idx->local('app/console doctrine:migrations:migrate --no-interaction --env=test');
             $idx->local('app/console doctrine:fixtures:load --no-interaction --env=test');
             $idx->runTask('cc');
        })->
                
    add('cc',
        function() use ($idx)
        {
             $idx->local('rm -Rf app/cache/*');
        })->
    
    add('chmod',
        function() use ($idx)
        {
            $idx->local("chmod -R 777 app/cache app/logs  app/files app/data app/sessions web/media");
            $idx->local("setfacl -Rn -m u:www-data:rwX -m u:`whoami`:rwX app/cache app/logs  app/files app/data app/sessions web/media");
            $idx->local("setfacl -dRn -m u:www-data:rwX -m u:`whoami`:rwX app/cache app/logs  app/files app/data app/sessions web/media");
        })->

    add('chmod:remote',
        function() use ($idx)
        {
            if (null === $idx->getCurrentTargetName()) {
                throw new \Exception("You must specify an environment [--env]");
            }

            $target = $idx->getCurrentTarget();

            if (!$target->get('deploy.remote_base_dir', false)) {
                throw new \Exception("No deploy parameters found. Check you configuration.");
            }

            $remoteBaseDir = $target->get('deploy.remote_base_dir');
            $dirs = array(
                '/current/app/cache',
                '/shared/app/logs',
                '/shared/app/files',
                '/shared/app/sessions',
                '/shared/web/media',
            );

            $remoteDirs = '';
            foreach($dirs as $dir) {
                $remoteDirs .= ' '.$remoteBaseDir.$dir;
            }

            $idx->remote("chmod -R 777 ".$remoteDirs);
            $idx->remote("setfacl -Rn -m u:www-data:rwX -m u:`whoami`:rwX ".$remoteDirs);
            $idx->remote("setfacl -dRn -m u:www-data:rwX -m u:`whoami`:rwX ".$remoteDirs);
        })->

    add('assets:install',
        function () use ($idx)
        {
            $idx->local("app/console assets:install --symlink --relative");
            $idx->local("app/console assetic:dump");
        })->

    add('touch',
        function () use ($idx)
        {
            $idx->local('echo " " >> src/Dan/MainBundle/Resources/public/less/index.less');
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

require 'idx/dan.php';

$idx->run();
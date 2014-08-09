<?php

use Symfony\Component\Yaml\Yaml;

$idx->
    add('remote2local',
        function() use ($idx)
        {
            $target = $idx->getCurrentTarget();
            $now = new \DateTime();

            if( $target === null ) {
                throw new \InvalidArgumentException("Target not provided. Please provide a valid target.");
            }

            $remoteBaseDir = $target->get('deploy.remote_base_dir');
            $localBaseDir = $target->get('deploy.local_base_dir');

            //IMAGES

            $remoteImagesDir = $remoteBaseDir.'/shared/app/files';
            $localImagesDir = $localBaseDir.'/app/files';

            $user = $target->get('ssh_params.user');
            $host = $idx->getCurrentTargetHost();

            $cmd = "rsync -Pavz --delete --progress -e 'ssh' $user@$host:$remoteImagesDir/ $localImagesDir";

            $idx->local($cmd);

            //DATABASE

            $dbHost = $target->get('database.host');
            $dbName = $target->get('database.name');
            $dbUsername = $target->get('database.username');
            $dbPassword = $target->get('database.password');

            $sqlFile = "/tmp/blab_".$now->getTimestamp();

            $idx->remote("mysqldump -u $dbUsername --password=$dbPassword --host=$dbHost $dbName | gzip -c > $sqlFile");

            $idx->local("scp $user@$host:$sqlFile $sqlFile");

            $idx->remote("rm $sqlFile");


            $parameters = Yaml::parse($localBaseDir.'/app/config/parameters.yml');
            $parameters = $parameters['parameters'];
            $dbHost = $parameters['database_host'];
            $dbName = $parameters['database_name'];
            $dbUsername = $parameters['database_user'];
            $dbPassword = $parameters['database_password'];

            $idx->local("zcat $sqlFile | mysql -h $dbHost -u $dbUsername --password=\"$dbPassword\" $dbName");

            $idx->local("rm $sqlFile");

        })
    ;
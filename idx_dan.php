<?php

$idx->
    add('remote2local',
        function() use ($idx)
        {
            $target = $idx->getCurrentTarget();

            if( $target === null ) {
                throw new \InvalidArgumentException("Target not provided. Please provide a valid target.");
            }

            $remoteBaseDir = $target->get('deploy.remote_base_dir');
            $localBaseDir = $target->get('deploy.local_base_dir');

            $remoteImagesDir = $remoteBaseDir.'/shared/app/files';
            $localImagesDir = $localBaseDir.'/app/files';

            $user = $target->get('ssh_params.user');
            $host = $idx->getCurrentTargetHost();

            $cmd = "rsync -Pavz --delete --progress -e 'ssh' $user@$host:$remoteImageDir/ $localImagesDir";

            return $idx->local($cmd);

        })
    ;
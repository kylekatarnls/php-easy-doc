<?php

namespace EasyDoc\Util;

class PharPublish extends GitHubApi
{
    public function publishPhar()
    {
        if (!EnvVar::toString('GITHUB_TOKEN')) {
            echo "PHAR publishing skipped as GITHUB_TOKEN is missing.\n";

            return;
        }

        // We get the releases from the GitHub API
        $releases = $this->json('releases');
        $releaseVersions = array_map(static function ($release) {
            return $release->tag_name;
        }, $releases);

        // we sort the releases with version_compare
        usort($releaseVersions, 'version_compare');

        // The total limit of all the phar files, size in bytes.
        // 94.371.840 B = 90 MB
        $totalLimitPharFiles = 94371840;

        // A counter for the total size for all the downloaded phar files.
        $totalPharSize = 0;

        // we iterate each version
        foreach ($releaseVersions as $version) {
            $pharUrl = 'releases/download/'.$version.'/phpmd.phar';
            $pharDestinationDirectory = $this->downloadDirectory.$version;
            @mkdir($pharDestinationDirectory, 0777, true);
            $this->download($pharDestinationDirectory.'/phpmd.phar', $pharUrl);
            $filesize = filesize($pharDestinationDirectory.'/phpmd.phar');

            echo $pharDestinationDirectory.'/phpmd.phar downloaded: '.number_format($filesize / 1024 / 1024, 2).' MB';

            if ($totalPharSize === 0) {
                echo ' (latest)';
                // the first one is the latest
                $latestPharDestinationDirectory = $this->downloadDirectory.'latest';
                @mkdir($latestPharDestinationDirectory, 0777, true);
                copy($pharDestinationDirectory.'/phpmd.phar', $latestPharDestinationDirectory.'/phpmd.phar');
                $totalPharSize += $filesize;
            }

            echo "\n";

            $totalPharSize += $filesize;

            if ($totalPharSize > $totalLimitPharFiles) {
                // we have reached the limit
                break;
            }
        }
    }
}

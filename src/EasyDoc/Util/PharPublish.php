<?php

namespace EasyDoc\Util;

use SimpleCli\Traits\Output;

class PharPublish extends GitHubApi
{
    protected function write(string $message, ?Output $output, string $color = null, string $background = null)
    {
        if (!$output) {
            echo $message;

            return;
        }

        $output->write($message, $color, $background);
    }

    public function publishPhar(Output $output = null)
    {
        if (!EnvVar::toString('GITHUB_TOKEN')) {
            $this->write("PHAR publishing skipped as GITHUB_TOKEN is missing.\n", $output, 'yellow');

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

            $this->write($pharDestinationDirectory.'/phpmd.phar downloaded: '.number_format($filesize / 1024 / 1024, 2).' MB', $output, 'light_green');

            if ($totalPharSize === 0) {
                $this->write(' (latest)', $output, 'light_green');
                // the first one is the latest
                $latestPharDestinationDirectory = $this->downloadDirectory.'latest';
                @mkdir($latestPharDestinationDirectory, 0777, true);
                copy($pharDestinationDirectory.'/phpmd.phar', $latestPharDestinationDirectory.'/phpmd.phar');
                $totalPharSize += $filesize;
            }

            $this->write("\n", $output, 'light_green');

            $totalPharSize += $filesize;

            if ($totalPharSize > $totalLimitPharFiles) {
                // we have reached the limit
                break;
            }
        }
    }
}

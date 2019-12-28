<?php

namespace EasyDoc\Util;

use SimpleCli\Writer;

class PharPublish extends GitHubApi implements PharPublisher
{
    /**
     * The total limit of all the phar files, size in bytes.
     * 94.371.840 B = 90 MB.
     *
     * @var int
     */
    protected $totalSizeLimit = 94371840;

    /**
     * Get the total limit of all the phar files, size in bytes.
     *
     * @return int
     */
    public function getTotalSizeLimit(): int
    {
        return $this->totalSizeLimit;
    }

    /**
     * Set the total limit of all the phar files, size in bytes.
     *
     * @param int $totalSizeLimit
     */
    public function setTotalSizeLimit(int $totalSizeLimit): void
    {
        $this->totalSizeLimit = $totalSizeLimit;
    }

    protected function write(string $message, ?Writer $output, string $color = null, string $background = null): void
    {
        if (!$output) {
            echo $message;

            return;
        }

        $output->write($message, $color, $background);
    }

    public function publishPhar(Writer $output = null, string $fileName = null): void
    {
        if (!EnvVar::toString('GITHUB_TOKEN')) {
            $this->write("PHAR publishing skipped as GITHUB_TOKEN is missing.\n", $output, 'yellow');

            return;
        }

        $fileName = $fileName ?: preg_replace('/^.*\/([^\/]+)$/', '$1', $this->defaultRepository).'.phar';

        // We get the releases from the GitHub API
        $releases = $this->json('releases');
        $releaseVersions = array_map(static function ($release): string {
            return $release->tag_name;
        }, $releases);

        // we sort the releases with version_compare
        usort($releaseVersions, 'version_compare');

        // A counter for the total size for all the downloaded phar files.
        $totalPharSize = 0;

        // we iterate each version
        foreach (array_reverse($releaseVersions) as $version) {
            $pharUrl = 'releases/download/'.$version.'/'.$fileName;
            $pharDestinationDirectory = $this->downloadDirectory.$version;
            @mkdir($pharDestinationDirectory, 0777, true);
            $this->download($pharDestinationDirectory.'/'.$fileName, $pharUrl);
            $filesize = filesize($pharDestinationDirectory.'/'.$fileName);

            $this->write($pharDestinationDirectory.'/'.$fileName.' downloaded: '.number_format($filesize / 1024 / 1024, 2).' MB', $output, 'light_green');

            if ($totalPharSize === 0) {
                $this->write(' (latest)', $output, 'light_green');
                // the first one is the latest
                $latestPharDestinationDirectory = $this->downloadDirectory.'latest';
                @mkdir($latestPharDestinationDirectory, 0777, true);
                copy($pharDestinationDirectory.'/'.$fileName, $latestPharDestinationDirectory.'/'.$fileName);
                $totalPharSize += $filesize;
            }

            $this->write("\n", $output, 'light_green');

            $totalPharSize += $filesize;

            if ($totalPharSize > $this->totalSizeLimit) {
                // we have reached the limit
                break;
            }
        }
    }
}

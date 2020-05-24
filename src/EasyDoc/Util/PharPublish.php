<?php

namespace EasyDoc\Util;

use SimpleCli\Writer;

class PharPublish extends GitHubApi implements PharPublisher, SizeLimiter, SizeChecker
{
    /**
     * The total limit of all the phar files, size in bytes.
     * 94.371.840 B = 90 MB.
     *
     * @var int
     */
    protected $totalSizeLimit = SizeLimiter::DEFAULT_MAXIMUM_SIZE;

    /**
     * The minimum size in bytes a PHAR file must have to be stored.
     *
     * @var int
     */
    protected $pharMinimumSize = SizeChecker::DEFAULT_MINIMUM_PHAR_SIZE;

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

    /**
     * Get the minimum size in bytes a PHAR file must have to be stored.
     *
     * @return int
     */
    public function getPharMinimumSize(): int
    {
        return $this->pharMinimumSize;
    }

    /**
     * Set the minimum size in bytes a PHAR file must have to be stored.
     *
     * @param int $totalSizeLimit
     */
    public function setPharMinimumSize(int $pharMinimumSize): void
    {
        $this->pharMinimumSize = $pharMinimumSize;
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
            $filePath = $pharDestinationDirectory.'/'.$fileName;
            $this->download($filePath, $pharUrl);
            $fileSize = filesize($filePath);
            $fileHumanSize = $this->getHumanSize($fileSize);

            if ($fileSize < $this->pharMinimumSize) {
                @unlink($filePath);
                @rmdir($pharDestinationDirectory);
                $threshold = $this->getHumanSize($this->pharMinimumSize);
                $this->write(
                    "$fileName skipped because it's only $fileHumanSize while at least $threshold is expected.",
                    $output,
                    'light_red'
                );

                continue;
            }

            $this->write($filePath.' downloaded: '.$fileHumanSize, $output, 'light_green');

            if ($totalPharSize === 0) {
                $this->write(' (latest)', $output, 'light_green');
                // the first one is the latest
                $latestPharDestinationDirectory = $this->downloadDirectory.'latest';
                @mkdir($latestPharDestinationDirectory, 0777, true);
                copy($pharDestinationDirectory.'/'.$fileName, $latestPharDestinationDirectory.'/'.$fileName);
                $totalPharSize += $fileSize;
            }

            $this->write("\n", $output, 'light_green');

            $totalPharSize += $fileSize;

            if ($totalPharSize > $this->totalSizeLimit) {
                // we have reached the limit
                break;
            }
        }
    }

    protected function write(string $message, ?Writer $output, string $color = null, string $background = null): void
    {
        if (!$output) {
            echo $message;

            return;
        }

        $output->write($message, $color, $background);
    }

    protected function getHumanSize(float $size): string
    {
        foreach (['', 'k', 'M', 'G'] as $prefix) {
            if ($size < 1024) {
                return $this->formatNumber($size).' '.$prefix.'B';
            }

            $size /= 1024;
        }

        return $this->formatNumber($size).' TB';
    }

    protected function formatNumber(float $number): string
    {
        return number_format($number, max(0, min(2, 2 - floor(log10($number)))));
    }
}

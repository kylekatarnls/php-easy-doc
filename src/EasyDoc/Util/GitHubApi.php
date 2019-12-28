<?php

namespace EasyDoc\Util;

class GitHubApi extends Http
{
    /**
     * Default GitHub repository.
     *
     * @var string
     */
    protected $defaultRepository;

    /**
     * Directory to download PHARs in.
     *
     * @var string
     */
    protected $downloadDirectory;

    /**
     * URL for web requests.
     *
     * @var string
     */
    protected $webUrl;

    /**
     * URL for API requests.
     *
     * @var string
     */
    protected $apiUrl;

    public function __construct(string $defaultRepository, string $downloadDirectory, string $webUrl = 'https://github.com/', string $apiUrl = 'https://api.github.com/repos/')
    {
        $this->defaultRepository = $defaultRepository;
        $this->downloadDirectory = $downloadDirectory;
        $this->webUrl = $webUrl;
        $this->apiUrl = $apiUrl;
    }

    protected function prefixRequest(string $prefix, string $url, string $repo = null, $data = null, bool $withToken = true, string $file = null)
    {
        $repo = $repo ?: $this->defaultRepository;

        return $this->request("$prefix$repo/$url", $data, $withToken, $file);
    }

    protected function webRequest(string $url, string $repo = null, $data = null, bool $withToken = true, string $file = null)
    {
        return $this->prefixRequest($this->webUrl, $url, $repo, $data, $withToken, $file);
    }

    protected function apiRequest(string $url, string $repo = null, $data = null, bool $withToken = true, string $file = null)
    {
        return $this->prefixRequest($this->apiUrl, $url, $repo, $data, $withToken, $file);
    }

    public function download(string $file, string $url, string $repo = null, $data = null, bool $withToken = true)
    {
        return $this->webRequest($url, $repo, $data, $withToken, $file);
    }

    public function json(string $url)
    {
        return json_decode($this->apiRequest($url, null, null, true));
    }
}
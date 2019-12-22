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

    public function __construct(string $defaultRepository, string $downloadDirectory)
    {
        $this->defaultRepository = $defaultRepository;
        $this->downloadDirectory = $downloadDirectory;
    }

    protected function prefixRequest(string $prefix, string $url, string $repo = null, $data = null, bool $withToken = true, string $file = null)
    {
        $repo = $repo ?: $this->defaultRepository;

        return $this->request("$prefix$repo/$url", $data, $withToken, $file);
    }

    protected function webRequest(string $url, string $repo = null, $data = null, bool $withToken = true, string $file = null)
    {
        return $this->prefixRequest('https://github.com/', $url, $repo, $data, $withToken, $file);
    }

    protected function apiRequest(string $url, string $repo = null, $data = null, bool $withToken = true, string $file = null)
    {
        return $this->prefixRequest('https://api.github.com/repos/', $url, $repo, $data, $withToken, $file);
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
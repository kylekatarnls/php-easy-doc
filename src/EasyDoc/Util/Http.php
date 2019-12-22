<?php

namespace EasyDoc\Util;

class Http
{
    public function request(string $url, $data = null, bool $withToken = false, string $file = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        if ($file !== null) {
            $file = fopen($file, 'w');

            if ($file !== false) {
                curl_setopt($curl, CURLOPT_FILE, $file);
            }
        }

        if ($data !== null) {
            $payload = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        if ($data !== null || $withToken) {
            $token = EnvVar::toString('GITHUB_TOKEN');

            if (!$token) {
                throw new RuntimeException('No Github token provided.');
            }

            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: token '.$token,
            ]);
        }

        $content = curl_exec($curl);
        $error = null;

        if (!$content) {
            $error = curl_error($curl);
        }

        curl_close($curl);

        if (is_resource($file)) {
            fclose($file);
        }

        if ($error !== null) {
            throw new RuntimeException("$url failed:\n$error");
        }

        return $content;
    }
}
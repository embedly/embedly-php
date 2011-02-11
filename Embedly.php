<?php
define('VERSION', '0.1.0');

class Embedly_API {
    private $hostname = 'api.embed.ly';
    private $key = null;
    private $_api_version = array(
        'oembed' => 1,
        'objectify' => 1,
        'preview' => 1
    );
    private $user_agent = "";

    public function __construct($args = array())
    {
        $args = array_merge(array(
            'user_agent' => sprintf("Mozilla/5.0 (compatible; embedly-php/%s)", VERSION),
            'key' => NULL,
            'hostname' => NULL,
            'api_version' => NULL
        ), $args);

        if ($args['user_agent']) {
            $this->user_agent = $args['user_agent'];
        }
        if ($args['key']) {
            $this->key = $args['key'];
            $this->_api_version['objectify'] = 2;
        }
        if ($args['hostname']) {
            $this->hostname = $args['hostname'];
        } else if ($args['key']) {
            $this->hostname = 'pro.embed.ly';
        }
        if ($args['api_version']) {
            $this->_api_version = array_merge($this->_api_version, $args['api_version']);
        }
    }
    /* Flexibly parse host strings.
     *
     * Returns an array of
     * { protocol:
     * , host:
     * , port:
     * , url:
     * }
     */
    private function parse_host($host) {
        $port = 80;
        $protocol = 'http';

        preg_match('/^(https?:\/\/)?([^\/]+)(:\d+)?\/?$/', $host, $matches);

        if (!$matches) {
            throw new Error(sprintf('invalid host %s', host));
        }

        $hostname = $matches[2];

        if ($matches[1] == 'https://') {
            $protocol = 'https';
        }

        if (array_key_exists(3, $matches) && $matches[3]) {
            $port = intval($matches[3]);
        } else if ($matches[1] == 'https://') {
            $port = 443;
        }

        $portpart = "";
        if (array_key_exists(3, $matches) && $matches[3]) {
            $portpart = sprintf(":%s", $matches[3]);
        }

        $url = sprintf("%s://%s%s/", $protocol, $hostname, $portpart);

        return array(
            'url' => $url,
            'scheme' => $protocol,
            'hostname' => $hostname,
            'port' => $port
        );
    }

    public function oembed($params)
    {
        return $this->apicall($this->_api_version['oembed'], 'oembed', $params);
    }

    public function preview($params)
    {
        return $this->apicall($this->_api_version['preview'], 'preview', $params);
    }

    public function objectify($params)
    {
        return $this->apicall($this->_api_version['objectify'], 'objectify', $params);
    }

    public function api_version()
    {
        return $this->_api_version;
    }

    public function apicall($version, $action, $params)
    {
        if (!array_key_exists('urls', $params)) {
            $params['urls'] = array();
        }
        if (array_key_exists('url', $params) && $params['url']) {
            array_push($params['urls'], $params['url']);
            delete($params['url']);
        }

        if ($this->key) {
            $params['key'] = $this->key;
        }

        $path = sprintf("%s/%s", $version, $action);
        $url_parts = $this->parse_host($this->hostname);
        $apiUrl = sprintf("%s%s?%s", $url_parts['url'], $path, $this->q($params));
        //print("\ncalling $apiUrl\n");

        $ch = curl_init($apiUrl);
        $this->setCurlOptions($ch, array(
            sprintf('Host: %s', $url_parts['hostname']),
            sprintf('User-Agent: %s', $this->user_agent)
        ));
        $res = $this->curlExec($ch);
        $result = json_decode($res);
        return $result;
    }

    private function q($params) {
        $pairs = array_map(function($key, $value) {
            $key = urlencode($key);
            if (is_array($value)) {
                $value = implode(',', array_map(function($i){
                    return urlencode($i);
                }, $value));
            } else {
                $value = urlencode($value);
            }
            return sprintf("%s=%s", $key, $value);
        }, array_keys($params), array_values($params));
        return implode('&', $pairs);
    }

    private function setCurlOptions(&$ch, $headers = array())
    {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 4096);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 25);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    }

    private function curlExec(&$ch)
    {
        $res = curl_exec($ch);
        if (false === $res) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        return $res;
    }
}

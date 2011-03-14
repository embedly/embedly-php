<?php
define('VERSION', '0.1.0');


//supporting functions
function reg_delim_stripper($r) {
    # we need to strip off regex delimeters and options to make
    # one giant regex
    return substr($r, 1, -2);
}


function reg_imploder($o) {
    return implode('|', array_map('reg_delim_stripper', $o->regex));
}


function _url_encoder_($key, $value) {
    $key = urlencode($key);
    if (is_array($value)) {
        $value = implode(',', array_map('urlencode', $value));
    } else {
        $value = urlencode($value);
    }
    return sprintf("%s=%s", $key, $value);
}


class Embedly_API {
    private $hostname = 'api.embed.ly';
    private $key = null;
    private $_api_version = array(
        'oembed' => 1,
        'objectify' => 2,
        'preview' => 1
    );
    private $user_agent = "";
    private $_services = NULL;

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

        if (!is_array($params['urls'])) {
            $urls = array($params['urls']);
            $params['urls'] = $urls;
        }

        if (array_key_exists('url', $params) && $params['url']) {
            array_push($params['urls'], $params['url']);
            unset($params['url']);
        }

        $rejects = array();
        if ($this->key) {
            $params['key'] = $this->key;
        } else {
            $regex = $this->services_regex();
            foreach ($params['urls'] as $i => $url) {
                $match = preg_match($regex, $url);
                if (!$match) {
                    //print("rejecting $url");
                    unset($params['urls'][$i]);
                    $rejects[$i] = (object)array(
                        'error_code' => '401',
                        'error_message' => 'This service requires an Embedly Pro account',
                        'type' => 'error'
                    );
                }
            };
        }

        $result = array();

        if (sizeof($rejects) < sizeof($params['urls'])) {
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
        }
        $merged_result = array();
        foreach ($result as $i => $v) {
            if (array_key_exists($i, $rejects)) {
                array_push($merged_result, array_shift($rejects));
            }
            array_push($merged_result, $v);
        };
        // grab any leftovers
        foreach ($rejects as $obj) {
            array_push($merged_result, $obj);
        }
        return $merged_result;
    }

    public function services() {
        if (!$this->_services) {
            $url = $this->parse_host($this->hostname);
            $apiUrl = sprintf("%s1/services/php", $url['url']);
            $ch = curl_init($apiUrl);
            $this->setCurlOptions($ch, array(
                sprintf('Host: %s', $url['hostname']),
                sprintf('User-Agent: %s', $this->user_agent)
            ));
            $res = $this->curlExec($ch);
            $this->_services = json_decode($res);
        }
        return $this->_services;
    }
    
    public function services_regex() {
    	$services = $this->services();
    	$regexes = array_map('reg_imploder', $services);
    	return '#'.implode('|', $regexes).'#i';
	}
        
    private function q($params) {
        $pairs = array_map('_url_encoder_', array_keys($params), array_values($params));
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

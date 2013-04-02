<?php

namespace Embedly;

/**
 *
 * @author Embed.ly, Inc.
 * @author Sven Eisenschmidt <sven.eisenschmidt@gmail.com>
 */
class Embedly {

    /**
     *
     * @const
     */
    const VERSION = '0.1.0';

    /**
     *
     * @var string
     */
    protected $hostname = 'api.embed.ly';

    /**
     *
     * @var string
     */
    protected $key = null;

    /**
     *
     * @var array
     */
    protected $api_version = array(
        'oembed' => 1,
        'objectify' => 2,
        'preview' => 1
    );

    /**
     *
     * @var string
     */
    protected $user_agent = "";

    /**
     *
     * @var array|object
     */
    protected $services = null;

    /**
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $args = array_merge(array(
            'user_agent' => sprintf("Mozilla/5.0 (compatible; embedly-php/%s)", self::VERSION),
            'key' => null,
            'hostname' => null,
            'api_version' => null
        ), $args);

        if ($args['user_agent']) {
            $this->user_agent = $args['user_agent'];
        }
        if ($args['key']) {
            $this->key = $args['key'];
        }
        if ($args['hostname']) {
            $this->hostname = $args['hostname'];
        }
        if ($args['api_version']) {
            $this->api_version = array_merge($this->api_version, $args['api_version']);
        }
    }

    /**
     *
     * Flexibly parse host strings.
     *
     * Returns an array of
     * { protocol:
     * , host:
     * , port:
     * , url:
     * }
     *
     * @param string $host
     * @return array
     */
    protected function parse_host($host)
    {
        $port = 80;
        $protocol = 'http';

        preg_match('/^(https?:\/\/)?([^\/]+)(:\d+)?\/?$/', $host, $matches);

        if (!$matches) {
            throw new \Exception(sprintf('invalid host %s', host));
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

    /**
     *
     * @return string|array
     */
    public function oembed($params)
    {
        return $this->apicall($this->api_version['oembed'], 'oembed', $params);
    }

    /**
     *
     * @param string|array $params
     * @return object
     */
    public function preview($params)
    {
        return $this->apicall($this->api_version['preview'], 'preview', $params);
    }

    /**
     *
     * @param array $params
     * @return object
     */
    public function objectify($params)
    {
        return $this->apicall($this->api_version['objectify'], 'objectify', $params);
    }

    /**
     *
     * @return string
     */
    public function api_version()
    {
        return $this->api_version;
    }

    /**
     *
     * @param string $version
     * @param array $action
     * @param array $params
     * @return object
     */
    public function apicall($version, $action, $params)
    {
        $justone = is_string($params);
        $params  = self::paramify($params);

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
                        'error_message' => 'This service requires an Embedly key',
                        'type' => 'error'
                    );
                }
            };
        }

        $result = array();

        if (sizeof($rejects) < sizeof($params['urls'])) {
            if (count($params['urls']) > 20) {
                throw new \Exception(
                    sprintf("Max of 20 urls can be queried at once, %s passed",
                    count($params['urls'])));
            }
            $path = sprintf("%s/%s", $version, $action);
            $url_parts = $this->parse_host($this->hostname);
            $apiUrl = sprintf("%s%s?%s", $url_parts['url'], $path, $this->q($params));

            $ch = curl_init($apiUrl);
            $this->setCurlOptions($ch, array(
                sprintf('Host: %s', $url_parts['hostname']),
                sprintf('User-Agent: %s', $this->user_agent)
            ));
            $res = $this->curlExec($ch);
            $result = json_decode($res) ?: array();
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

        if($justone) {
            return array_shift($merged_result);
        }

        return $merged_result;
    }

    /**
     *
     * @return array
     */
    public function services() {
        if (!$this->services) {
            $url = $this->parse_host($this->hostname);
            $apiUrl = sprintf("%s1/services/php", $url['url']);
            $ch = curl_init($apiUrl);
            $this->setCurlOptions($ch, array(
                sprintf('Host: %s', $url['hostname']),
                sprintf('User-Agent: %s', $this->user_agent)
            ));
            $res = $this->curlExec($ch);
            $this->services = json_decode($res);
        }
        return $this->services;
    }

    /**
     *
     * @return string
     */
    public function services_regex() {
    	$services = $this->services();
    	$regexes = array_map(array(__CLASS__, 'reg_imploder'), $services);
    	return '#'.implode('|', $regexes).'#i';
	}

    /**
     *
     * @return string
     */
    protected function q($params) {
        $pairs = array_map(array(__CLASS__, 'url_encode'), array_keys($params), array_values($params));
        print implode('&', $pairs);
        return implode('&', $pairs);
    }

    /**
     *
     * @param resource $ch
     * @param array $headers
     * @return void
     */
    protected function setCurlOptions(&$ch, $headers = array())
    {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 4096);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 25);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    }

    /**
     *
     * @param resource $ch
     * @return string
     */
    protected function curlExec(&$ch)
    {
        $res = curl_exec($ch);
        if (false === $res) {
            throw new \Exception(curl_error($ch), curl_errno($ch));
        }
        return $res;
    }


    /**
     *
     * @param string $r
     * @return string
     */
    public static function reg_delim_stripper($r)
    {
        # we need to strip off regex delimeters and options to make
        # one giant regex
        return substr($r, 1, -2);
    }

    /**
     *
     * @param stdClass $o
     * @return string
     */
    public static function reg_imploder(\stdClass $o)
    {
        return implode('|', array_map(array(__CLASS__, 'reg_delim_stripper'), $o->regex));
    }

    /**
     *
     * @param string $key
     * @param string|array $value
     * @return string
     */
    public static function url_encode($key, $value)
    {
        $key = urlencode($key);
        if (is_array($value)) {
            $value = implode(',', array_map('urlencode', $value));
        } else {
            $value = urlencode($value);
        }
        return sprintf("%s=%s", $key, $value);
    }

    /**
     *
     * @param string $input
     * @return array
     */
    public static function paramify($input)
    {
        if(is_string($input)) {
            return array('urls' => $input);
        }

        return $input;
    }
}

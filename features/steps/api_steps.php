<?php
require_once('Embedly.php');


$steps->Given('/an embedly api$/',  function($world) {
    $world->embedlyapi = new Embedly_API();
    $world->api = $world->embedlyapi;
});

$steps->Given('/an embedly api with key/',  function($world) {
    if (getenv('EMBEDLY_KEY') === null) {
        throw new Exception('Please set env variable $EMBEDLY_KEY');
    }
    $world->embedlypro = new Embedly_API(array(
        'key' => getenv('EMBEDLY_KEY')
    ));
    $world->api = $world->embedlypro;
});

$steps->When('/(\w+) is called with the (.*) URLs?( and ([^\s]+) flag)?$/', function($world, $method, $urls, $_=null, $flag=null) {
    $world->result = null;
    try {
        $urls = explode(',', $urls);
        $opts = array(
            'urls' => $urls
        );
        if ($flag != null) {
            $opts[$flag] = TRUE;
        }
        $world->result = $world->api->$method($opts);
    } catch(Exception $e) {
        $world->error = $e;
    }
});

$steps->Then('/objectify api_version is (\d+)$/', function($world, $version) {
    $api_version = $world->api->api_version();
    assertEquals($api_version['objectify'], $version);
});

$steps->Then('/([^\s]+) should be (.+)$/', function($world, $key, $value) {
    if ($world->error) {
        throw $world->error;
    }

    $results = array_map(function($o) use ($key){
        if (property_exists($o, $key)) {
            return $o->$key;
        } else {
            return '';
        }
    }, $world->result);
    assertEquals(implode(',', $results), $value);
});

$steps->Then('/([^\s]+) should start with ([^\s]+)/', function($world, $key, $value) {
    if ($world->error) {
        throw $world->error;
    }
    $result = array_reduce(explode('.', $key), function($o, $k) {
        if (property_exists($o, $k)) {
            return $o->$k;
        } else {
            return '';
        }
    }, $world->result[0]);
    assertStringStartsWith($value, $result);
});

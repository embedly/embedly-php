<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

require_once __DIR__ . '/../../src/Embedly/Embedly.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

    /**
     * @Given /^an embedly api with key$/
     */
    public function anEmbedlyApiWithKey3()
    {
        if (getenv('EMBEDLY_KEY') === null) {
            throw new Exception('Please set env variable $EMBEDLY_KEY');
        }
        print getenv('EMBEDLY_KEY');
        $this->embedlypro = new Embedly\Embedly(array(
            'key' => getenv('EMBEDLY_KEY')
        ));
        $this->api = $this->embedlypro;
    }

    /**
     * @Then /([^\s]+) should be (.+)$/
     */
    public function shouldBe($key, $value)
    {
        if (property_exists($this, 'error')) {
            throw $this->error;
        }

        $this->result ?: array();
        assertNotEmpty($this->result, 'No results received.');

        $results = array_map(function($o) use ($key){
            if (property_exists($o, $key)) {
                return $o->$key;
            } else {
                return '';
            }
        }, $this->result);
        assertEquals(implode(',', $results), $value);
    }

    /**
     * @Given /^an embedly api$/
     */
    public function anEmbedlyApi()
    {
        $this->embedlyapi = new Embedly\Embedly();
        $this->api = $this->embedlyapi;
    }

    /**
     * @Then /objectify api_version is (\d+)$/
     */
    public function checkObjectifyVersion($version)
    {
        $api_version = $this->api->api_version();
        assertEquals($api_version['objectify'], $version);
    }

    /**
     * @When /^(\w+) is called with the (.*) URLs?( and ([^\s]+) flag)?$/
     */
    public function oembedIsCalled($method, $urls, $_=null, $flag=null)
    {
        $this->result = null;
        try {
            $urls = explode(',', $urls);
            $opts = array(
                'urls' => $urls
            );
            if ($flag != null) {
                $opts[$flag] = TRUE;
            }
            $this->result = $this->api->$method($opts);
        } catch(Exception $e) {
            throw $e;
            $this->error = $e;
        }
    }
    /**
     * @Then /([^\s]+) should start with ([^\s]+)/
     */
    public function shouldStartWith($key, $value) {
        if (property_exists($this, 'error')) {
            throw $this->error;
        }

        $this->result ?: array();
        assertNotEmpty($this->result, 'No results received.');

        $result = array_reduce(explode('.', $key), function($o, $k) {
            if (property_exists($o, $k)) {
                return $o->$k;
            } else {
                return '';
            }
        }, $this->result[0]);

        assertStringStartsWith($value, $result);
    }
}

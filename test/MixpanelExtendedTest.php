<?php

require_once(dirname(__FILE__) . "/../MixpanelExtended.php");

class MixpanelExtendedTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $_SERVER['HTTP_USER_AGENT'] = "Mozilla/5.0 (iPad; CPU OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B329 Safari/8536.25"; // Safari iOS 6.1 – iPad
        $_SERVER['HTTP_REFERRER'] = 'https://bing.com?q=proudsugar';

        $cookie = json_decode(urldecode('%7B%22distinct_id%22%3A%20%2234%22%2C%22%24initial_referrer%22%3A%20%22%24direct%22%2C%22%24initial_referring_domain%22%3A%20%22%24direct%22%2C%22utm_source%22%3A%20%22test%22%2C%22utm_campaign%22%3A%20%22test%22%2C%22utm_term%22%3A%20%22test%22%2C%22__mps%22%3A%20%7B%7D%2C%22__mpso%22%3A%20%7B%7D%2C%22__mpa%22%3A%20%7B%7D%2C%22__mpap%22%3A%20%5B%5D%7D'), true);

        $this->instance = new MixpanelExtended($cookie);
    }

    public function tearDown()
    {
        unset($this->instance);
    }

    public function testOs()
    {
        $this->assertEquals($this->instance->os(), 'iOS');
    }

    public function testDevice()
    {
        $this->assertEquals($this->instance->device(), 'iPad');
    }

    public function testBrowser()
    {
        $this->assertEquals($this->instance->browser(), 'Mobile Safari');
    }

    public function testReferringDomain()
    {
        $this->assertEquals($this->instance->referringDomain(), 'bing.com?q=proudsugar');
    }

    public function testSearchInfo()
    {
        $p = $this->instance->searchInfo();
        $this->assertTrue(array_key_exists('$search_engine', $p));
        $this->assertTrue(array_key_exists('mp_keyword', $p));
        $this->assertEquals($p['$search_engine'], 'bing');
        $this->assertEquals($p['mp_keyword'], 'proudsugar');
    }

    public function testCampaignParams()
    {
        $url = 'http://www.proudsugar.com?utm_source=source&utm_medium=medium&utm_campaign=campaign&utm_content=content&utm_term=term';
        $p = $this->instance->campaignParams($url);
        $this->assertCount(5, $p);
    }

    public function testGetProperties()
    {
        $expected = array(
            '$os' => 'iOS',
            '$browser' => 'Mobile Safari',
            '$device' => 'iPad',
            '$referrer' => $_SERVER['HTTP_REFERRER'],
            '$referring_domain' => 'bing.com?q=proudsugar',
            '$initial_referrer' => '$direct',
            '$initial_referring_domain' => '$direct',
            'utm_source' => 'test',
            'utm_campaign' => 'test',
            'utm_term' => 'test'
        );
        $properties = $this->instance->getProperties(true);
        $this->assertEquals($properties, $expected);
    }

}
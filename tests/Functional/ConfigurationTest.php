<?php

namespace Tests\Functional;

class ConfigurationTest extends BaseTestCase
{
    protected $usePackageVfs = false;

    public function testTimezone()
    {
        $timezone = 'America/New_York';
        $settings = $this->getSettings();
        $settings['settings']['timezone'] = $timezone;

        $this->request('GET', '/verify', $settings);

        $this->assertEquals($timezone, date_default_timezone_get());
    }
}

<?php

class ConfigTest extends TestCase
{
    public function testAppURL()
    {
        $app_url = config('app.url');
        $this->assertFalse(strpos($app_url, 'http://http://'));
        $this->assertFalse(strpos($app_url, 'https://https://'));
    }
}

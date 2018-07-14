<?php

class PrivacyPolicyTest extends TestCase
{
    public function testGet()
    {
        $response = $this->get('/privacy-policy');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Privacy Policy') !== false);
    }
}

<?php

class PrivacyPolicyTest extends TestCase
{
    public function testGet()
    {
        $content = $this->get('/privacy-policy')->seeStatusCode(200)->response->getContent();
        $this->assertTrue(strpos($content, 'Privacy Policy') !== false);
    }
}

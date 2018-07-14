<?php

class TermsOfUseTest extends TestCase
{
    public function testGet()
    {
        $response = $this->get('/terms-of-use');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Terms of Use') !== false);
    }
}

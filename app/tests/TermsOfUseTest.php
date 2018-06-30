<?php

class TermsOfUseTest extends TestCase
{
    public function testGet()
    {
        $content = $this->get('/terms-of-use')->seeStatusCode(200)->response->getContent();
        $this->assertTrue(strpos($content, 'Terms of Use') !== false);
    }
}

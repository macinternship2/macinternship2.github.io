<?php

class HomeTest extends TestCase
{
    public function testGet()
    {
        $response = $this->get('/');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Sign in') !== false);
        $this->assertTrue(strpos($content, 'Sign up') !== false);
        $this->assertTrue(strpos($content, 'Search by keyword(s)') !== false);
    }
}

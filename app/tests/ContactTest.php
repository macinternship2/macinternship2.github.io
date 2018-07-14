<?php

class ContactTest extends TestCase
{
    public function testGet()
    {
        $response = $this->get('/contact');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Contact') !== false);
        $this->assertTrue(strpos($content, 'Your Comment') !== false);
    }
}

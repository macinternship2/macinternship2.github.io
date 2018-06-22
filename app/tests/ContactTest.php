<?php

class ContactTest extends TestCase
{
    public function testGet()
    {
		$content = $this->get('/contact')->seeStatusCode(200)->response->getContent();
		$this->assertTrue(strpos($content, 'Contact') !== false);
		$this->assertTrue(strpos($content, 'Your Comment') !== false);
    }
}

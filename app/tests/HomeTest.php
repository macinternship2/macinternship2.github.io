<?php

class HomeTest extends TestCase
{
    public function testGet()
    {
		$content = $this->get('/')->seeStatusCode(200)->response->getContent();
		$this->assertTrue(strpos($content, 'Sign in / Sign up') !== false);
		$this->assertTrue(strpos($content, 'Search by keyword(s)') !== false);
    }
}

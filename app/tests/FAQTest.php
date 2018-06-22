<?php

class FAQTest extends TestCase
{
    public function testGet()
    {
		$content = $this->get('/faq')->seeStatusCode(200)->response->getContent();
		$this->assertTrue(strpos($content, 'Frequently Asked Questions') !== false);
		$this->assertTrue(strpos($content, 'What is AccessLocator?') !== false);
    }
}

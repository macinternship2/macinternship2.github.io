<?php

class FAQTest extends TestCase
{
    public function testGet()
    {
        $response = $this->get('/faq');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Frequently Asked Questions') !== false);
        $this->assertTrue(strpos($content, 'What is AccessLocator?') !== false);
    }
}

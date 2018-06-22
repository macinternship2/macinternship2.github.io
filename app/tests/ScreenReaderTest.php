<?php

class ScreenReaderTest extends TestCase
{
    /**
     * Tests that the api/is-using-screen-reader API.
	 * 
     * @return void
     */
    public function testGet()
    {
		$content = $this->get('/api/is-using-screen-reader')->seeStatusCode(200)->response->getContent();
		$value = json_decode($content);
		$this->assertInternalType('bool', $value);
    }
}

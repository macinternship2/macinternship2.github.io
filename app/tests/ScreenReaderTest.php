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
        $response = $this->get('/api/is-using-screen-reader');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $value = json_decode($content);
        $this->assertInternalType('bool', $value);
    }
}

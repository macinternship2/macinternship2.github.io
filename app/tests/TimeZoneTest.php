<?php

class TimeZoneTest extends TestCase
{
    public function testPost()
    {
        $data = ['time_zone_offset' => 0];
        $response = $this->post('/time-zone', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $response_data = json_decode($content);
        $this->assertInternalType('object', $response_data);
        $this->assertTrue($response_data->success);
    }
}

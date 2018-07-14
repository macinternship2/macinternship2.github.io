<?php

class RegionsTest extends TestCase
{
    public function testGet()
    {
        $response = $this->get('/api/regions');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $value = json_decode($content);
        $this->assertInternalType('array', $value);
        foreach ($value as $region) {
            $this->assertInternalType('object', $region);
            $this->assertInternalType('int', $region->id);
            $this->assertInternalType('int', $region->country_id);
            $this->assertInternalType('string', $region->name);
        }
    }
}

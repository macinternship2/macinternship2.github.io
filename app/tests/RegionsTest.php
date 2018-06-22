<?php

class RegionsTest extends TestCase
{
    public function testGet()
    {
		$content = $this->get('/api/regions')->seeStatusCode(200)->response->getContent();
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

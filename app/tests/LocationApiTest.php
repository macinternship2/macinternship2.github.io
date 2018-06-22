<?php

class LocationApiTest extends TestCase
{
    /**
	 * Checks that the HTTP GET api returns the expected format
	 * for /api/locations
     *
     * @return void
     */
    public function testGet()
    {
		$locations_content = $this->get('/api/locations')->seeStatusCode(200)->response->getContent();
		$locations_data = json_decode($locations_content);
		$this->assertTrue(is_array($locations_data));
		foreach ($locations_data as $location) {
			$this->assertTrue(is_object($location));
			$this->assertTrue(is_int($location->data_source_id));
			$this->assertTrue($location->owner_user_id === NULL);
			$this->assertTrue($location->name === NULL || is_string($location->name));
			$this->assertTrue(is_int($location->longitude) || is_float($location->longitude));
			$this->assertTrue(is_int($location->longitude) || is_float($location->longitude));
		}
    }
}

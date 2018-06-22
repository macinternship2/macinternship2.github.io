<?php

class LocationSearchRadiusTest extends TestCase
{
	public static function useDistance($testCase, $testDistance)
	{
		$data = ['distance' => $testDistance];
		$response_content = $testCase->post('/api/set-search-radius', $data)->seeStatusCode(200)->response->getContent();
		$response_data = json_decode($response_content);
		$testCase->assertTrue(is_object($response_data));
		$testCase->assertTrue(isset($response_data->message));

		// Check that the radius was set.
		$search_content = $testCase->get('/location-search?location_tag_id=1')->seeStatusCode(200)->response->getContent();
		$pos = strpos($search_content, 'placeholder="distance" value="'.$testDistance.'"');
		$testCase->assertTrue($pos !== false);
	}

    /**
	 * Tests that the set search radius API used in the location search feature works.
     *
     * @return void
     */
    public function testPost()
    {
		self::useDistance($this, 0.5);
		self::useDistance($this, 1);

		// Invalid due to not specifying a distance.
		$data = [];
		$this->post('/api/set-search-radius', $data)->seeStatusCode(422);

		// Invalid due to being negative.
		$data = ['distance' => -1];
		$this->post('/api/set-search-radius', $data)->seeStatusCode(422);

		// Invalid distance due to being an invalid number.
		$data = ['distance' => 'bobby'];
		$this->post('/api/set-search-radius', $data)->seeStatusCode(422);
	}
}

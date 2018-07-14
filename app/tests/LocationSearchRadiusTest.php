<?php

class LocationSearchRadiusTest extends TestCase
{
    public static function useDistance($testCase, $testDistance)
    {
        $data = ['distance' => $testDistance];
        $response = $testCase->post('/api/set-search-radius', $data);
        $testCase->assertEquals(200, $response->getStatusCode());
        $response_content = $response->getContent();
        $response_data = json_decode($response_content);
        $testCase->assertTrue(is_object($response_data));
        $testCase->assertTrue(isset($response_data->message));

        // Check that the radius was set.
        $response = $testCase->get('/location-search?location_tag_id=1');
        $testCase->assertEquals(200, $response->getStatusCode());
        $search_content = $response->getContent();
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
        $response = $this->post('/api/set-search-radius', $data);
        $this->assertEquals(422, $response->getStatusCode());

        // Invalid due to being negative.
        $data = ['distance' => -1];
        $response = $this->post('/api/set-search-radius', $data);
        $this->assertEquals(422, $response->getStatusCode());

        // Invalid distance due to being an invalid number.
        $data = ['distance' => 'bobby'];
        $response = $this->post('/api/set-search-radius', $data);
        $this->assertEquals(422, $response->getStatusCode());
    }
}

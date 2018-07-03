<?php

class LocationRatingsCacheTest extends TestCase
{
    private function processCache()
    {
        $content = $this->post('/api/populate-ratings-cache')->seeStatusCode(200)->response->getContent();
        $response_data = json_decode($content);
        return $response_data;
    }
    
    /**
     * Tests a rating cache calculation
     *
     * @return void
     */
    public function testPost()
    {
        $response_data = $this->processCache();
        $this->assertTrue(is_object($response_data));
        $this->assertTrue(isset($response_data->number_rated));
        $this->assertInternalType('int', $response_data->number_rated);
        $this->assertInternalType('int', $response_data->number_unrated);
        $this->assertTrue($response_data->number_rated >= 0);
        $this->assertTrue($response_data->number_unrated >= 0);

        // Check that progress was made.
        $number_remaining = $response_data->number_unrated;
        $response_data = $this->processCache();
        if ($number_remaining > 0) {
            $this->assertTrue($number_remaining > $response_data->number_unrated);
        }
    }
}

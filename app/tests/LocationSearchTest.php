<?php

class LocationSearchTest extends TestCase
{
    private function checkLocationSearchTableContent($content)
    {
        $requiredSubstrings = ['Location Search Results', 'Radius'];
        foreach ($requiredSubstrings as $requiredSubstring) {
            $this->assertTrue(strpos($content, $requiredSubstring) !== false);
        }
    }
    
    public function testSortByName()
    {
        $response = $this->get('/location-search?location_tag_id=1&keywords=&order_by=name');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'spreadsheet sort-by-name') !== false);
        $this->checkLocationSearchTableContent($content);
    }

    public function testSortByDistance()
    {
        $response = $this->get('/location-search?location_tag_id=1&keywords=&order_by=distance');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'spreadsheet sort-by-distance') !== false);
        $this->checkLocationSearchTableContent($content);
    }

    public function testSortByRating()
    {
        $response = $this->get('/location-search?location_tag_id=1&keywords=&order_by=rating');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'spreadsheet sort-by-rating') !== false);
        $this->checkLocationSearchTableContent($content);
    }

    public function testMapView()
    {
        $response = $this->get('/location-search?keywords=&order_by=rating&location_tag_id=1&view=map');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Location Search Results') !== false);
        $this->assertTrue(strpos($content, ' id="map"') !== false);
    }
}

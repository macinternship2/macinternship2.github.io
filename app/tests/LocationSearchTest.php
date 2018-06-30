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
        $content = $this->get('/location-search?location_tag_id=1&keywords=&order_by=name')->
            seeStatusCode(200)->response->getContent();
        $this->assertTrue(strpos($content, 'spreadsheet sort-by-name') !== false);
        $this->checkLocationSearchTableContent($content);
    }

    public function testSortByDistance()
    {
        $content = $this->get('/location-search?location_tag_id=1&keywords=&order_by=distance')->
            seeStatusCode(200)->response->getContent();
        $this->assertTrue(strpos($content, 'spreadsheet sort-by-distance') !== false);
        $this->checkLocationSearchTableContent($content);
    }

    public function testSortByRating()
    {
        $content = $this->get('/location-search?location_tag_id=1&keywords=&order_by=rating')->
            seeStatusCode(200)->response->getContent();
        $this->assertTrue(strpos($content, 'spreadsheet sort-by-rating') !== false);
        $this->checkLocationSearchTableContent($content);
    }

    public function testMapView()
    {
        $content = $this->get('/location-search?keywords=&order_by=rating&location_tag_id=1&view=map')->
            seeStatusCode(200)->response->getContent();
        $this->assertTrue(strpos($content, 'Location Search Results') !== false);
        $this->assertTrue(strpos($content, ' id="map"') !== false);
    }
}

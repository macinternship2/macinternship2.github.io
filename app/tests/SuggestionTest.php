<?php


class SuggestionTest extends TestCase
{
    /**
     * Test adding a suggestion successfully
     */
    public function testAddSuggestion()
    {
        $this->flushSession();
        $data = ['email' => 'josh.greig2@gmail.com', 'password' => 'password'];
        $response = $this->post('/signin', $data);
        $this->assertEquals(302, $response->getStatusCode());
        $data = [
            'location-id' => '00000000-0000-0000-0000-000000009146',
            'location-name' => 'testname',
            'phone-number' => '226-961-3209',
            'address'=> 'testaddress',
            'url' => 'http://www.google.com'
        ];
        $response = $this->post('/api/add-suggestion', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJson([
            'success' => 2,
        ]);
    }

    public function testAddSuggestionWithEmptyUrl()
    {
        $this->flushSession();
        $data = ['email' => 'josh.greig2@gmail.com', 'password' => 'password'];
        $response = $this->post('/signin', $data);
        $this->assertEquals(302, $response->getStatusCode());
        $data = [
            'location-id' => '00000000-0000-0000-0000-000000009146',
            'location-name' => 'testname',
            'phone-number' => '226-961-3209',
            'address'=> 'testaddress',
            'url' => ''
        ];
        $response = $this->post('/api/add-suggestion', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJson([
            'success' => 2,
        ]);
    }

    public function testAddSuggestionWithInvalidParameters()
    {
        $this->flushSession();
        $data = ['email' => 'josh.greig2@gmail.com', 'password' => 'password'];
        $response = $this->post('/signin', $data);
        $this->assertEquals(302, $response->getStatusCode());
        $data = [
            'location-id' => '00000000-0000-0000-0000-000000009146',
            'location-name' => 'testname',
            'phone-number' => '123-456-4',
            'address'=> 'test',
            'url' => 'abc@gmail.com'
        ];
        $response = $this->post('/api/add-suggestion', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJson([
            'success' => 1,
        ]);
    }

    /**
     * Test adding a suggestion without signing in
     */

    public function testNotSignIn()
    {
        $this->flushSession();
        $data = [
            'location-id' => '00000000-0000-0000-0000-000000009146',
            'location-name' => 'testname',
            'phone-number' => 'testphone',
            'address'=> 'testaddress',
            'url' => 'www.testurlurl.com'
        ];
        $response = $this->post('/api/add-suggestion', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJson([
            'success' => 0,
        ]);
    }
}

<?php

class SuggestionTest extends TestCase
{
    private function signOut()
    {
        $response = $this->get('/signout');
        $this->assertEquals(302, $response->getStatusCode());
    }

    private function signIn()
    {
        $this->signOut();
        $data = ['email' => 'josh.greig2@gmail.com', 'password' => 'password'];
        $response = $this->post('/signin', $data);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testAddSuggestionAvailableOnLocationReport()
    {
        Session::start();
        $this->signIn();
        $response = $this->get('/location/report/00000000-0000-0000-0000-000000000001');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'suggestion-form') !== false);
    }

    /**
     * Test adding a suggestion successfully
     */
    public function testAddSuggestion()
    {
        Session::start();
        $this->signIn();
        $data = [
            'location-id' => '00000000-0000-0000-0000-000000009146',
            'location-name' => 'testname',
            'phone-number' => '226-961-3209',
            'address'=> 'testaddress',
            'url' => 'http://www.google.com',
            '_token' => csrf_token()
        ];
        $response = $this->post('/api/add-suggestion', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJson([
            'success' => 1
        ]);
    }

    public function testAddSuggestionWithEmptyUrl()
    {
        Session::start();
        echo 'csrf = '. csrf_token();
        $this->signIn();
        $data = [
            'location-id' => '00000000-0000-0000-0000-000000009146',
            'location-name' => 'testname',
            'phone-number' => '226-961-3209',
            'address'=> 'testaddress',
            'url' => '',
            '_token' => csrf_token()
        ];
        $response = $this->post('/api/add-suggestion', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJson([
            'success' => 1
        ]);
    }

    public function testAddSuggestionWithInvalidParameters()
    {
        Session::start();
        $this->signIn();
        $data = [
            'location-id' => '00000000-0000-0000-0000-000000009146',
            'location-name' => 'testname',
            'phone-number' => '123-456-4',
            'address'=> 'test',
            'url' => 'abc@gmail.com',
            '_token' => csrf_token()
        ];
        $response = $this->post('/api/add-suggestion', $data);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJson([
            'success' => 0
        ]);
    }

    /**
     * Test adding a suggestion without signing in
     */
    public function testNotSignIn()
    {
        Session::start();
        $this->signOut();
        $data = [
            'location-id' => '00000000-0000-0000-0000-000000009146',
            'location-name' => 'testname',
            'phone-number' => 'testphone',
            'address'=> 'testaddress',
            'url' => 'www.testurlurl.com',
            '_token' => csrf_token()
        ];
        $response = $this->post('/api/add-suggestion', $data);
        $this->assertEquals(403, $response->getStatusCode());
        $response->assertJson([
            'success' => 0
        ]);
    }
}

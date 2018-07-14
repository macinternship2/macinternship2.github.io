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
        $response->assertResponseStatus(302);
        $data = [
            'location-id' => '00000000-0000-0000-0000-000000009146',
            'location-name' => 'testname',
            'phone-number' => 'testphone',
            'address'=> 'testaddress',
            'url' => 'testurl'
        ];
        $response = $this->post('/add-suggestion', $data);
        $redirectUrl = $this->response->headers->get('Location');
        $response->assertResponseStatus(302);
        $this->assertTrue(strpos($redirectUrl, 'location-report/00000000-0000-0000-0000-000000009146') !== false);
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
            'url' => 'testurl'
        ];
        $response = $this->post('/add-suggestion', $data);
        $this->assertResponseStatus(302);
        $redirectUrl = $this->response->headers->get('Location');
        $response->assertResponseStatus(302);
        $this->assertTrue(strpos($redirectUrl, 'signin') !== false);
    }
}

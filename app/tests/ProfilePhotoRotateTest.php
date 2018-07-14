<?php

class ProfilePhotoRotateTest extends TestCase
{
    public function testGetPhoto()
    {
        // Not signed in so should be redirected.
        $response = $this->get('/profile-photo');
        $this->assertEquals(302, $response->getStatusCode());
    }
}

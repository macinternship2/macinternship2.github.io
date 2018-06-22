<?php

class ProfilePhotoRotateTest extends TestCase
{
    public function testGetPhoto()
    {
		// Not signed in so should be redirected.
		$this->get('/profile-photo')->seeStatusCode(302);
    }
}

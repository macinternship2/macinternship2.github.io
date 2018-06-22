<?php

class PasswordChangeTest extends TestCase
{
    public function testGet()
    {
		// Not signed in so should be redirected.
		$this->get('/change-password')->seeStatusCode(302);
    }
}

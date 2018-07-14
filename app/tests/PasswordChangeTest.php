<?php

class PasswordChangeTest extends TestCase
{
    public function testGet()
    {
        // Not signed in so should be redirected.
        $this->assertEquals(302, $this->get('/change-password')->getStatusCode());
    }
}

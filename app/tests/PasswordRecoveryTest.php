<?php

class PasswordRecoveryTest extends TestCase
{
    public function testGetForm()
    {
		$content = $this->get('/password-recovery')->seeStatusCode(200)->response->getContent();
		$this->assertTrue(strpos($content, 'Password Recovery') !== false);
    }
}

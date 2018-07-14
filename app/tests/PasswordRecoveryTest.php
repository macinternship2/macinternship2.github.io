<?php

class PasswordRecoveryTest extends TestCase
{
    public function testGetForm()
    {
        $response = $this->get('/password-recovery');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Password Recovery') !== false);
    }
}

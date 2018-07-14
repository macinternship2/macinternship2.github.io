<?php

class QuestionExplainationTest extends TestCase
{
    public function testGet()
    {
        $response = $this->get('/api/question-explanation/1');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $value = json_decode($content);
        $this->assertInternalType('object', $value);
        $this->assertInternalType('string', $value->html);
    }
}

<?php

class QuestionExplainationTest extends TestCase
{
    public function testGet()
    {
		$content = $this->get('/api/question-explanation/1')->seeStatusCode(200)->response->getContent();
		$value = json_decode($content);
		$this->assertInternalType('object', $value);
		$this->assertInternalType('string', $value->html);
    }
}

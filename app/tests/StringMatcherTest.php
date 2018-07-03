<?php
/**
StringMatcherTest tests aspects of helpers/StringMatcher.php.
*/
use App\Libraries\StringMatcher;

class StringMatcherTest extends TestCase
{
    public function testSingleSpace()
    {
        $tests = [
            ['in' => 'Hello World', 'out' => 'Hello World'],
            ['in' => 'Hello  World', 'out' => 'Hello World'],
            ['in' => 'Hello   World', 'out' => 'Hello World'],
            ['in' => ' Hello  World', 'out' => ' Hello World'],
            ['in' => '  Hello  World', 'out' => ' Hello World'],
            ['in' => 'Hello  World  ', 'out' => 'Hello World '],
            ['in' => "Hello  \t\r\nWorld  \t", 'out' => 'Hello World '],
            ['in' => "Hello\t\t\r\nWorld \t\r\n", 'out' => 'Hello World ']
        ];
        foreach ($tests as $singleSpaceTest) {
            $this->assertEquals(StringMatcher::singleSpace($singleSpaceTest['in']), $singleSpaceTest['out']);
        }
    }
}

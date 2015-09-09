<?php

namespace Phalcana\Tests;

use Phalcana\Unittest\TestCase,
    Phalcana\Debug;

/**
 * Tests Kohana Core
 *
 * @TODO Use a virtual filesystem (see phpunit doc on mocking fs) for find_file etc.
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.debug
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class DebugTest extends TestCase
{

    /**
     * Provides test data for test_debug()
     *
     * @return array
     */
    public function provider_vars()
    {
        return array(
            // $thing, $expected
            array(array('foobar'), "<pre class=\"debug\"><small>array</small><span class=\"len\">(1)</span> <span>(\n    <span class=\"int\">0</span> <span class=\"pointer\">=></span> <small>string</small><span class=\"len\">(6)</span> <span class=\"string\">\"foobar\"</span>\n)</span></pre>"),
        );
    }

    /**
     * Tests Debug::vars()
     *
     * @test
     * @dataProvider provider_vars
     * @covers Debug::vars
     * @param boolean $thing    The thing to debug
     * @param boolean $expected Output for Debug::vars
     */
    public function test_var($thing, $expected)
    {
        $this->assertEquals($expected, Debug::vars($thing));
    }

    /**
     * Provides test data for testDebugPath()
     *
     * @return array
     */
    public function provider_debug_path()
    {
        return array(
            array(
                SYSPATH.'classes'.DIRECTORY_SEPARATOR.'kohana'.'.php',
                'SYSPATH/classes/kohana.php'
            ),
            array(
                MODPATH.'unittest/classes/kohana/unittest/runner.php',
                'MODPATH/unittest/classes/kohana/unittest/runner.php'
            ),
        );
    }

    /**
     * Tests Debug::path()
     *
     * @test
     * @dataProvider provider_debug_path
     * @covers Debug::path
     * @param boolean $path     Input for Debug::path
     * @param boolean $expected Output for Debug::path
     */
    public function test_debug_path($path, $expected)
    {
        $this->assertEquals($expected, Debug::path($path));
    }

    /**
     * Provides test data for test_dump()
     *
     * @return array
     */
    public function provider_dump()
    {
        return array(
            array('foobar', 128, 10, '<small>string</small><span class="len">(6)</span> <span class="string">"foobar"</span>'),
            array('foobar', 2, 10, '<small>string</small><span class="len">(6)</span> <span class="string">"fo&nbsp;&hellip;"</span>'),
            array(null, 128, 10, '<small>null</small>'),
            array(true, 128, 10, '<small>bool</small> <span class="bool">true</span>'),
            array(array('foobar'), 128, 10, "<small>array</small><span class=\"len\">(1)</span> <span>(\n    <span class=\"int\">0</span> <span class=\"pointer\">=></span> <small>string</small><span class=\"len\">(6)</span> <span class=\"string\">\"foobar\"</span>\n)</span>"),
            array(new \StdClass, 128, 10, "<small>object</small> <span>stdClass(0)</span> <code>{\n}</code>"),
            array("fo\x6F\xFF\x00bar\x8F\xC2\xB110", 128, 10, '<small>string</small><span class="len">(10)</span> <span class="string">"foobar±10"</span>'),
            array(array('level1' => array('level2' => array('level3' => array('level4' => array('value' => 'something'))))), 128, 4,
"<small>array</small><span class=\"len\">(1)</span> <span>(\n    <span class=\"string\">\"level1\"</span> <span class=\"pointer\">=></span> <small>array</small><span class=\"len\">(1)</span> <span>(\n        <span class=\"string\">\"level2\"</span> <span class=\"pointer\">=></span> <small>array</small><span class=\"len\">(1)</span> <span>(\n            <span class=\"string\">\"level3\"</span> <span class=\"pointer\">=></span> <small>array</small><span class=\"len\">(1)</span> <span>(\n                <span class=\"string\">\"level4\"</span> <span class=\"pointer\">=></span> <small>array</small><span class=\"len\">(1)</span> (\n                    ...\n                )\n            )</span>\n        )</span>\n    )</span>\n)</span>"),
        );
    }

    /**
     * Tests Debug::dump()
     *
     * @test
     * @dataProvider provider_dump
     * @covers Debug::dump
     * @covers Debug::_dump
     * @param object $exception exception to test
     * @param string $expected  expected output
     */
    public function test_dump($input, $length, $limit, $expected)
    {
        $this->assertEquals($expected, Debug::dump($input, $length, $limit));
    }

}

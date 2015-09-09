<?php

namespace Phalcana\Tests;

use Phalcana\Unittest\TestCase,
    Phalcana\File;

/**
 * Tests Kohana File helper
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.file
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class FileTest extends TestCase
{

    /**
     * Provides test data for testSplitJoin()
     *
     * @return array
     */
    public function providerSplitJoin()
    {
        return array(
            // $value, $result
            array(\Phalcana\Phalcana::$di->get('fs')->findFile('tests', 'test_data/github', 'png'), .01, 1),
        );
    }

    /**
     * Tests File::mime()
     *
     * @test
     * @dataProvider providerSplitJoin
     * @param boolean $input    Input for File::split
     * @param boolean $peices   Input for File::split
     * @param boolean $expected Output for File::splut
     */
    public function testSplitJoin($input, $peices, $expected)
    {
        $this->assertSame($expected, File::split($input, $peices));
        $this->assertSame($expected, File::join($input));

        foreach (glob(\Phalcana\Phalcana::$di->get('fs')->findFile('tests', 'test_data/github', 'png').'.*') as $file) {
            unlink($file);
        }
    }
}

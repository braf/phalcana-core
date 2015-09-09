<?php

namespace Phalcana\Tests;

use Phalcana\Unittest\TestCase,
    Phalcana\Inflector;

/**
 * Tests Phalcana inflector class
 *
 * @group phalcana
 * @group phalcana.core
 * @group phalcana.core.inflector
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class InflectorTest extends TestCase
{

    /**
     * Provides test data for test_lang()
     *
     * @return array
     */
    public function providerUncountable()
    {
        return array(
            // $value, $result
            array('fish', true),
            array('cat', false),
            array('deer', true),
            array('bison', true),
            array('friend', false),
        );
    }

    /**
     * Tests Inflector::uncountable
     *
     * @test
     * @dataProvider providerUncountable
     * @param boolean $input  Input for File::mime
     * @param boolean $expected Output for File::mime
     */
    public function testUncountable($input, $expected)
    {
        $this->assertSame($expected, Inflector::uncountable($input));
    }

    /**
     * Provides test data for test_lang()
     *
     * @return array
     */
    public function providerSingular()
    {
        return array(
            // $value, $result
            array('fish', null, 'fish'),
            array('cats', null, 'cat'),
            array('cats', 2, 'cats'),
            array('cats', '2', 'cats'),
            array('children', null, 'child'),
            array('meters', 0.6, 'meters'),
            array('meters', 1.6, 'meters'),
            array('meters', 1.0, 'meter'),
            array('status', null, 'status'),
            array('statuses', null, 'status'),
            array('heroes', null, 'hero'),
        );
    }

    /**
     * Tests Inflector::singular
     *
     * @test
     * @dataProvider providerSingular
     * @param boolean $input  Input for File::mime
     * @param boolean $expected Output for File::mime
     */
    public function testSingular($input, $count, $expected)
    {
        $this->assertSame($expected, Inflector::singular($input, $count));
    }

    /**
     * Provides test data for test_lang()
     *
     * @return array
     */
    public function providerPlural()
    {
        return array(
            // $value, $result
            array('fish', null, 'fish'),
            array('cat', null, 'cats'),
            array('cats', 1, 'cats'),
            array('cats', '1', 'cats'),
            array('movie', null, 'movies'),
            array('meter', 0.6, 'meters'),
            array('meter', 1.6, 'meters'),
            array('meter', 1.0, 'meter'),
            array('hero', null, 'heroes'),
            array('Dog', null, 'Dogs'), // Titlecase
            array('DOG', null, 'DOGS'), // Uppercase
        );
    }

    /**
     * Tests Inflector::plural
     *
     * @test
     * @dataProvider providerPlural
     * @param boolean $input  Input for File::mime
     * @param boolean $expected Output for File::mime
     */
    public function testPlural($input, $count, $expected)
    {
        $this->assertSame($expected, Inflector::plural($input, $count));
    }

    /**
     * Provides test data for test_camelize()
     *
     * @return array
     */
    public function providerCamelize()
    {
        return array(
            // $value, $result
            array('mother cat', 'camelize', 'motherCat'),
            array('kittens in bed', 'camelize', 'kittensInBed'),
            array('mother cat', 'underscore', 'mother_cat'),
            array('kittens in bed', 'underscore', 'kittens_in_bed'),
            array('kittens-are-cats', 'humanize', 'kittens are cats'),
            array('dogs_as_well', 'humanize', 'dogs as well'),
        );
    }

    /**
     * Tests Inflector::camelize
     *
     * @test
     * @dataProvider providerCamelize
     * @param boolean $input  Input for File::mime
     * @param boolean $expected Output for File::mime
     */
    public function testCamelize($input, $method, $expected)
    {
        $this->assertSame($expected, Inflector::$method($input));
    }

    /**
     * Provides data for testDecamelize()
     *
     * @return array
     */
    public function providerDecamelize()
    {
        return array(
            array('getText', '_', 'get_text'),
            array('getJSON', '_', 'get_json'),
            array('getLongText', '_', 'get_long_text'),
            array('getI18N', '_', 'get_i18n'),
            array('getL10n', '_', 'get_l10n'),
            array('getTe5t1ng', '_', 'get_te5t1ng'),
            array('OpenFile', '_', 'open_file'),
            array('CloseIoSocket', '_', 'close_io_socket'),
            array('fooBar', ' ', 'foo bar'),
            array('camelCase', '+', 'camel+case'),
        );
    }

    /**
     * Tests Inflector::decamelize()
     *
     * @test
     * @dataProvider providerDecamelize
     * @param string $input Camelized string
     * @param string $glue Glue
     * @param string $expected Expected string
     */
    public function testDecamelize($input, $glue, $expected)
    {
        $this->assertSame($expected, Inflector::decamelize($input, $glue));
    }
}

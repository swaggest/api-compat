<?php

namespace Swaggest\ApiCompat\Tests;


use Swaggest\ApiCompat\Path;

class TestPath extends \PHPUnit_Framework_TestCase
{
    public function testFitsPattern()
    {
        $this->assertFalse(Path::fitsPattern(
            '#/paths/%2Fpet%2F%7BpetId%7D/post/parameters/2/required',
            '#/paths/*/*/parameters/*/name'
        ));

        $this->assertFalse(Path::fitsPattern(
            '#/paths/%2Fpet%2F%7BpetId%7D/post/parameters/2/name/3',
            '#/paths/*/*/parameters/*/name'
        ));

        $this->assertTrue(Path::fitsPattern(
            '#/paths/%2Fpet%2F%7BpetId%7D/post/parameters/2/name',
            '#/paths/*/*/parameters/*/name'
        ));

        $this->assertTrue(Path::fitsPattern(
            '#/paths/%2Fpet%2F%7BpetId%7D/post/parameters/2/required',
            '#/paths/*/*/parameters/...'
        ));
    }

}
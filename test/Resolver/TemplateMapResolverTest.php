<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Resolver;

use ArrayObject;
use PHPUnit_Framework_TestCase as TestCase;
use Laminas\View\Resolver\TemplateMapResolver;

class TemplateMapResolverTest extends TestCase
{
    public function testMapIsEmptyByDefault()
    {
        $resolver = new TemplateMapResolver();
        $this->assertEquals(array(), $resolver->getMap());
    }

    public function testCanSeedMapWithArrayViaConstructor()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $this->assertEquals($map, $resolver->getMap());
    }

    public function testCanSeedMapWithTraversableViaConstructor()
    {
        $map = new ArrayObject(array('foo/bar' => __DIR__ . '/foo/bar.phtml'));
        $resolver = new TemplateMapResolver($map);
        $this->assertEquals($map->getArrayCopy(), $resolver->getMap());
    }

    public function testCanSeedMapWithArrayViaSetter()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver();
        $resolver->setMap($map);
        $this->assertEquals($map, $resolver->getMap());
    }

    public function testCanSeedMapWithTraversableViaSetter()
    {
        $map = new ArrayObject(array('foo/bar' => __DIR__ . '/foo/bar.phtml'));
        $resolver = new TemplateMapResolver();
        $resolver->setMap($map);
        $this->assertEquals($map->getArrayCopy(), $resolver->getMap());
    }

    public function testCanAppendSingleEntriesViaAdd()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $resolver->add('foo/baz', __DIR__ . '/../foo/baz.phtml');
        $expected = array_merge($map, array('foo/baz' => __DIR__ . '/../foo/baz.phtml'));
        $this->assertEquals($expected, $resolver->getMap());
    }

    public function testCanAppendMultipleEntriesAsArrayViaAdd()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $more = array(
            'foo/baz' => __DIR__ . '/../foo/baz.phtml',
            'baz/bat' => __DIR__ . '/baz/bat.phtml',
        );
        $resolver->add($more);
        $expected = array_merge($map, $more);
        $this->assertEquals($expected, $resolver->getMap());
    }

    public function testCanAppendMultipleEntriesAsTraversableViaAdd()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $more = new ArrayObject(array(
            'foo/baz' => __DIR__ . '/../foo/baz.phtml',
            'baz/bat' => __DIR__ . '/baz/bat.phtml',
        ));
        $resolver->add($more);
        $expected = array_merge($map, $more->getArrayCopy());
        $this->assertEquals($expected, $resolver->getMap());
    }

    public function testCanAppendMultipleEntriesAsArrayViaMerge()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $more = array(
            'foo/baz' => __DIR__ . '/../foo/baz.phtml',
            'baz/bat' => __DIR__ . '/baz/bat.phtml',
        );
        $resolver->merge($more);
        $expected = array_merge($map, $more);
        $this->assertEquals($expected, $resolver->getMap());
    }

    public function testCanAppendMultipleEntriesAsTraversableViaMerge()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $more = new ArrayObject(array(
            'foo/baz' => __DIR__ . '/../foo/baz.phtml',
            'baz/bat' => __DIR__ . '/baz/bat.phtml',
        ));
        $resolver->merge($more);
        $expected = array_merge($map, $more->getArrayCopy());
        $this->assertEquals($expected, $resolver->getMap());
    }

    public function testCanMergeTwoMaps()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $more = new TemplateMapResolver(array(
            'foo/baz' => __DIR__ . '/../foo/baz.phtml',
            'baz/bat' => __DIR__ . '/baz/bat.phtml',
        ));
        $resolver->merge($more);
        $expected = array_merge($map, $more->getMap());
        $this->assertEquals($expected, $resolver->getMap());
    }

    public function testAddOverwritesMatchingEntries()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $more = array(
            'foo/bar' => __DIR__ . '/../foo/baz.phtml',
            'baz/bat' => __DIR__ . '/baz/bat.phtml',
        );
        $resolver->merge($more);
        $expected = array_merge($map, $more);
        $this->assertEquals($expected, $resolver->getMap());
        $this->assertEquals(__DIR__ . '/../foo/baz.phtml', $resolver->get('foo/bar'));
    }

    public function testMergeOverwritesMatchingEntries()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $more = new TemplateMapResolver(array(
            'foo/bar' => __DIR__ . '/../foo/baz.phtml',
            'baz/bat' => __DIR__ . '/baz/bat.phtml',
        ));
        $resolver->merge($more);
        $expected = array_merge($map, $more->getMap());
        $this->assertEquals($expected, $resolver->getMap());
        $this->assertEquals(__DIR__ . '/../foo/baz.phtml', $resolver->get('foo/bar'));
    }

    public function testHasReturnsTrueWhenMatchingNameFound()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $this->assertTrue($resolver->has('foo/bar'));
    }

    public function testHasReturnsFalseWhenNameHasNoMatch()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $this->assertFalse($resolver->has('bar/baz'));
    }

    public function testGetReturnsPathWhenNameHasMatch()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $this->assertEquals($map['foo/bar'], $resolver->get('foo/bar'));
    }

    public function testGetReturnsFalseWhenNameHasNoMatch()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $this->assertFalse($resolver->get('bar/baz'));
    }

    public function testResolveReturnsPathWhenNameHasMatch()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $this->assertEquals($map['foo/bar'], $resolver->resolve('foo/bar'));
    }

    public function testResolveReturnsFalseWhenNameHasNoMatch()
    {
        $map = array('foo/bar' => __DIR__ . '/foo/bar.phtml');
        $resolver = new TemplateMapResolver($map);
        $this->assertFalse($resolver->resolve('bar/baz'));
    }
}

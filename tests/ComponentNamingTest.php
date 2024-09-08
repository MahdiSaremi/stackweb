<?php

namespace StackWeb\Tests;

use Illuminate\Support\Str;
use StackWeb\ComponentNaming;

class ComponentNamingTest extends TestCase
{

    public function test_view_to_component()
    {
        $this->assertSame(
            'FooNamespace::FooDirectory.BarName:FooSubject',
            ComponentNaming::viewToComponent('foo-namespace::foo-directory.bar-name:foo-subject')
        );
    }

    public function test_component_to_view()
    {
        $this->assertSame(
            'foo-namespace::foo-directory.bar-name',
            ComponentNaming::componentToView('FooNamespace::FooDirectory.BarName:FooSubject')
        );
    }

    public function test_split_component_name_parts()
    {
        $this->assertSame(
            ['Namespace', 'Path.Name', 'Subject'],
            ComponentNaming::splitComponent('Namespace::Path.Name:Subject')
        );
        $this->assertSame(
            [null, 'Path.Name', 'Subject'],
            ComponentNaming::splitComponent('Path.Name:Subject')
        );
        $this->assertSame(
            ['Namespace', 'Path.Name', null],
            ComponentNaming::splitComponent('Namespace::Path.Name')
        );
        $this->assertSame(
            [null, 'Path.Name', null],
            ComponentNaming::splitComponent('Path.Name')
        );
    }

    public function test_implode_component_name_parts()
    {
        $this->assertSame(
            'Namespace::Path.Name:Subject',
            ComponentNaming::implodeComponent('Namespace', 'Path.Name', 'Subject')
        );
        $this->assertSame(
            'Path.Name:Subject',
            ComponentNaming::implodeComponent(null, 'Path.Name', 'Subject')
        );
        $this->assertSame(
            'Namespace::Path.Name',
            ComponentNaming::implodeComponent('Namespace', 'Path.Name', null)
        );
        $this->assertSame(
            'Path.Name',
            ComponentNaming::implodeComponent(null, 'Path.Name', null)
        );
    }

}
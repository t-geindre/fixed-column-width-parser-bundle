<?php

namespace Zol\Bundle\FixedColumnWidthParserBundle\DependencyInjection\tests\units;

use mageekguy\atoum\test;
use Zol\Bundle\FixedColumnWidthParserBundle\DependencyInjection\ZolFixedColumnWidthParserExtension as Base;

use Symfony\Component\DependencyInjection\Definition;



class ZolFixedColumnWidthParserExtension extends test
{
    public function getBaseInstance()
    {
        return new Base;
    }

    public function getContainerBuilderMock()
    {
        $mock = new \mock\Symfony\Component\DependencyInjection\ContainerBuilder;

        return $mock;
    }


    public function testLoadFail()
    {
        $extension = $this->getBaseInstance();
        $container = $this->getContainerBuilderMock();

        // Invalid configuration directory
        $this
            ->exception(function() use($extension, $container) {
                $extension->load([['schemas' => [ __DIR__.'/foo/bar/' ]]], $container);
            })
                ->isInstanceOf('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException')
                ->hasMessage('schema option must contains readable files or directory')
        ;

        // Schema validation fail
        $dir = __DIR__.'/../fixtures/fail/';

        $this
            ->exception(function() use($extension, $container, $dir) {
                $extension->load([['schemas' => [ $dir ]]], $container);
            })
                ->isInstanceOf('Zol\Parser\FixedColumnWidth\SchemaValidationException')
                ->hasMessage(sprintf(
                    'An error occured while parsing %s/Item.yml: ignore option must be null or an array, boolean given',
                    realpath($dir)
                ));
        ;

        $file = __DIR__.'/../fixtures/fail/Item.yml';

        $this
            ->exception(function() use($extension, $container, $file) {
                $extension->load([['schemas' => [ $file ]]], $container);
            })
                ->isInstanceOf('Zol\Parser\FixedColumnWidth\SchemaValidationException')
                ->hasMessage(sprintf(
                    'An error occured while parsing %s: ignore option must be null or an array, boolean given',
                    realpath($file)
                ));
        ;
    }

    public function testLoadSuccess()
    {
        $extension = $this->getBaseInstance();
        $container = $this->getContainerBuilderMock();

        $extension->load([['schemas' => [ __DIR__.'/../fixtures/valid/' ]]], $container);

        $this
            ->array($container->getParameter('zol.parser.fixed_column_width.schema.item'))
                ->isEqualTo(['entry' => [ 'id' => 2, 'name' => 25 ]])

            ->array($container->getParameter('zol.parser.fixed_column_width.schema.foo.bar.object'))
                ->isEqualTo(['entry' => [ 'key' => 5, 'title' => 100 ]])

            ->array($container->getParameter('zol.parser.fixed_column_width.schema.foo.bar.other.object'))
                ->isEqualTo(['entry' => [ 'reference' => 3, 'definition' => 50 ]])

            ->object($definition = $container->getDefinition('zol.parser.fixed_column_width.item'))

            ->string($definition->getClass())
                ->isEqualTo('%zol.parser.fixed_column_width.class%')

            ->array($definition->getArguments())
                ->isEqualTo(['%zol.parser.fixed_column_width.schema.item%'])
        ;
    }
}

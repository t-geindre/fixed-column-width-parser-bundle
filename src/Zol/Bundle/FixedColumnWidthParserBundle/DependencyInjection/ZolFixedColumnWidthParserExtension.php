<?php

namespace Zol\Bundle\FixedColumnWidthParserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\DependencyInjection\Definition;
use Zol\Parser\FixedColumnWidth\SchemaValidationException;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ZolFixedColumnWidthParserExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!empty($config['schemas'])) {
            foreach ($config['schemas'] as $schema) {
                if (is_dir($schema)) {
                    $schema = glob(sprintf('%s/*.yml', $schema));
                } else if (is_file($schema)) {
                        $schema = [$schema];
                } else {
                    throw new InvalidConfigurationException(sprintf(
                        'schema option must contains readable files or directory'
                    ));
                }

                $this->loadSchemas($schema, $container);
            }
        }
    }

    protected function loadSchemas(array $files, ContainerBuilder $container)
    {
        $parser = new YamlParser;
        $validator = $container->get('zol.schema_validator.fixed_column_width');

        foreach ($files as $file) {

            $file = realpath($file);
            $schemas = $parser->parse(file_get_contents($file));

            foreach ($schemas as $key => $schema) {

                try {
                    $validator->validateSchema($schema, true);
                } catch (SchemaValidationException $e) {
                    throw new SchemaValidationException(sprintf(
                        'An error occured while parsing %s: %s',
                        $file,
                        $e->getMessage()
                    ));
                }

                $schemaKey = sprintf('zol.parser.fixed_column_width.schema.%s', $key);
                $container->setParameter($schemaKey, $schema);

                $container->setDefinition(
                    sprintf('zol.parser.fixed_column_width.%s', $key),
                    new Definition(
                        '%zol.parser.fixed_column_width.class%',
                        ['%'.$schemaKey.'%']
                    )
                );
            }
        }
    }
}

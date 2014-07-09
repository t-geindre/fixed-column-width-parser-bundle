# Fixed column width parser bundle [![Build Status](https://travis-ci.org/t-geindre/fixed-column-width-parser-bundle.svg?branch=master)](https://travis-ci.org/t-geindre/fixed-column-width-parser-bundle)

Provide Symfony integration for [fixed-column-width-parser library](https://github.com/t-geindre/fixed-column-width-parser).

## Installation

Via [Composer](https://getcomposer.org/) :

```shell
$ composer install zol/fixed-column-width-parser-bundle
```

Then, enable the bundle in your AppKernel :

```php
public function registerBundles()
{
    $bundles = array(
        // [...]
        new Zol\Bundle\FixedColumnWidthParserBundle\ZolFixedColumnWidthParserBundle()
    );

    return $bundles;
}
```

## Configuration

Bundle configuration is optionnal. Each defined schema will be read and validated to create a parser service with pre configured schema.

```yml
zol_fixed_column_width_parser:
    # An array of schemas
    schemas:
        # If it's a directory, bundle will load all YAML files found in this directory
        -  %kernel.root_dir%/../src/Zol/MyBundle/Resources/config/schemas/
        -  %kernel.root_dir%/../src/Zol/OtherBundle/Resources/config/schemas/Item.yml
```

## Schema reference

At the moment, this bundle only support YAML schema format. This schema reference might be outdated, see [library schema reference](https://github.com/t-geindre/fixed-column-width-parser#schema-reference) for updated informations.

```yml
# The following key will be used to define the parser service name
# Here, the service name will be : zol.parser.fixed_column_width.item
item:
    # Ignored lines, null if none
    # First line is indexed by 1
    # Optionnal, null by default
    ignore: [1, 8 , 9]

    # Header line, null if missing
    # Optionnal, null by default
    header:
        field-name: length
        field-name: length

    # Define entry schema
    # Required
    entry:
        field-name: length
        field-name: length

    # Use header values as entry field names
    # If true, entry field names will be replaced with header values
    # Optionnal, false by default
    header-as-field-name: false

    # Ignore empty line
    # Optionnal, true by default
    ignore-empty-lines: true

    # Multiple files in one
    # If true, you must define separator
    # Optionnal, default false,
    multiple: false

    # Separator, only used if multiple is true
    # Define files separator
    separator:
        field: length # Separator field
        values: [ 'value', 'value'] # Field values considered as separator
        ignore: true # Ignore separation line
```

## Usage

### Schema validation

This bundle provide a schema validation service : `zol.schema_validator.fixed_column_width`.

```php
$validator = $container->get('zol.schema_validator.fixed_column_width');

// Throw \Zol\Parser\FixedColumnWidth\SchemaValidationException
$validator->validateSchema([], true);

// Return false
$validator->validateSchema([], false);

// Return true
$validator->validateSchema(['entry' => [1]], true);
```

### Parser

This bundle provide a generic parser service : `zol.parser.fixed_column_width`. But you can also use a pre configured parser service which will be named according to your YAML schema definition : `zol.parser.fixed_column_width.item` for instance.

```php
// Generic parser
$genericParser = $container->get('zol.parser.fixed_column_width');
$genericParser->parse('file.dat', ['entry' => [1]]); // return array file content

// Configuration defined parser
$itemParser = $container->get('zol.parser.fixed_column_width.item');
$itemParser->parse('item.dat'); // return array file content
```

## Tests

```shell
$ ./vendor/bin/atoum
```

MakerGeneratorBundle
=====================

This bundle brings back the possibility of generating bundle code in Symfony 4 and newer.
It also supports the new bundle-less directory structure as created by Symfony Flex. 

The `MakerGeneratorBundle` enhance [`MakerBundle`](https://github.com/symfony/maker-bundle) by providing new `--namespace` option for all `make:*` commands 
and new interactive and intuitive commands for generating
code skeletons like bundles, or CRUD actions based on a Doctrine 2 schema. 
Most of the code was adopted from old well-known `SensioGeneratorBundle`.
The boilerplate code provided by these code generators
will save you a large amount of time and work.

Installation
------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require popov/maker-generator-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles for the
`dev` environment in the `config/bundles.php` file of your project:
```php
// config/bundles.php
return [
    // ...
    Popov\MakerGeneratorBundle\PopovMakerGeneratorBundle::class => ['dev' => true],
];
```

List of Available Commands
--------------------------

All the commands provided by this bundle can be run in interactive or
non-interactive mode. The interactive mode asks you some questions to configure
the command parameters that actually generate the code.

Read the following articles to learn how to use the new commands:
 - [Generating a New Bundle Skeleton](Resources/doc/commands/generate_bundle.md)
 - ~[Generating a New Action](#)~

Overriding Skeleton Templates
-----------------------------

All generators use a template skeleton to generate files. By default, the
commands use templates provided by the bundle under its ``Resources/skeleton/``
directory.

You can define custom skeleton templates by creating the same directory and
file structure in the following locations (displayed from highest to lowest
priority):

* ``<BUNDLE_PATH>/Resources/SensioGeneratorBundle/skeleton/``
* ``resources/SensioGeneratorBundle/skeleton/``

The ``<BUNDLE_PATH>`` value refers to the base path of the bundle where you are
scaffolding an action or a CRUD backend.

For instance, if you want to override the `edit` template for the CRUD
generator, create a `crud/views/edit.html.twig.twig` file under
`resources/MakerGeneratorBundle/skeleton/`.

When overriding a template, have a look at the default templates to learn more
about the available templates, their paths and the variables they have access.

Instead of copy/pasting the original template to create your own, you can also
extend it and only override the relevant parts:

```twig
{# resources/MakerGeneratorBundle/skeleton/crud/actions/create.php.twig #}

{# notice the "skeleton" prefix here -- more about it below #}
{% extends "skeleton/crud/actions/create.php.twig" %}

{% block phpdoc_header %}
   {{ parent() }}
   *
   * This is going to be inserted after the phpdoc title
   * but before the annotations.
{% endblock phpdoc_header %}
```

Complex templates in the default skeleton are split into Twig blocks to allow
easy inheritance and to avoid copy/pasting large chunks of code.

In some cases, templates in the skeleton include other ones, like
in the `crud/views/edit.html.twig.twig` template for instance:

```twig
{{ include('crud/views/others/record_actions.html.twig.twig') }}
```

If you have defined a custom template for this template, it is going to be
used instead of the default one. But you can explicitly include the original
skeleton template by prefixing its path with `skeleton/` like we did above:

```twig
{{ include('skeleton/crud/views/others/record_actions.html.twig.twig') }}
```

You can learn more about this neat "trick" in the official [Twig documentation](http://twig.sensiolabs.org/doc/recipes.html#overriding-a-template-that-also-extends-itself).

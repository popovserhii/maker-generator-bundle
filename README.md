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

#### Generating a New Bundle Skeleton
_Basic usage_
```bash
$ php bin/console make:bundle --namespace=App/BlogBundle
```
[Detailed overview](MakerGeneratorBundle/Resources/doc/commands/generate_bundle.md)

#### Generating a New Action
_Basic usage_
@todo
 
~[Detailed overview](#)~

#### Other Embedded Commands
[From official docs](https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html#usage): 
This bundle provides several commands under the make: namespace. List them all executing this command:
```bash
$ php bin/console list make


 make:command            Creates a new console command class
 make:controller         Creates a new controller class
 make:entity             Creates a new Doctrine entity class

 [...]

 make:validator          Creates a new validator and constraint class
 make:voter              Creates a new security voter class
```

The names of the commands are self-explanatory, but some of them include optional arguments and options. 
Check them out with the `--help` option:
```bash
 php bin/console make:controller --help
```

Usage Strategies
----------------

#### Default strategy
By default, Symfony 4 and newer provide bundle-less structure under `App` namespace.
So all your code will have only `App` namespace. If you want to change the namespace, read the next chapter.

With minimum efforts, in standard Symfony configuration, you can just add `--namespace` option to a command
and get well-formatted code.

Take a note, this approach won't create the real bundle for you, but just create bundle like directory structure.
It has some benefits, because there is no need create additional bundle configuration files, 
until you want to publish it on Packages, etc. 

Anyway, in any moment you can add required configuration files and get the real bundle without copy-pasting and 
rebuilding files from standard Symfony approach.    

After you run the next command, the new Entity will be generated in `src/` directory 
with namespace `App\BlogBundle\Entity\Post`

```bash
$ php bin/console make:entity Post --namespace=App/BlogBundle
```

##### DI configuration
Add the next configuration to `config/services.yaml`.
This tells Symfony to make classes in `src/App/BlogBundle` available to be used as services. 
This creates a service per class whose id is the fully-qualified class name.

> NOTE: You have to add this configuration for each new namespace. 
> If you use bundle-based structure, then add this to bundle configuration.

```yaml
# config/services.yaml
services:
    # ...

    # makes classes in src/ available to be used as services
    # this creates a service per class whose id is the fully-qualified class name
    App\BlogBundle\:
        resource: '../src/App/BlogBundle/*'
        exclude: '../src/App/BlogBundle/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}'

    # controllers are imported separately to make sure services can be injected
    # as action arguments even if you don't extend any base controller class
    App\BlogBundle\Controller\:
        resource: '../src/App/BlogBundle/Controller'
        tags: ['controller.service_arguments']
```

#### Bundle strategy
Bundle structure allows grouping related code under certain directory.
There is no need scroll through dozens or even thousands of files to find what you need.
This approach makes code more readable and understandable. 

This approach works as previous one, but before create any class with `make:*` command 
you must generate bundle structure:

```bash
$ php bin/console make:bundle --namespace=App/BlogBundle
```

Changing Default Namespace
----------------------------
In some cases you might not to use default `App` namespace, and instead of you want to use your personal namespace
or your company namespace, then you have to change `App\Kernel` namespace to something else in the following files:

- `src/Kernel.php`
- `public/index.php`
- `bin/console`

Overriding Skeleton Templates
-----------------------------

All generators use a template skeleton to generate files. By default, the
commands use templates provided by the bundle under its ``Resources/skeleton/``
directory.

You can define custom skeleton templates by creating the same directory and
file structure in the following locations (displayed from highest to lowest
priority):

* ``<BUNDLE_PATH>/Resources/MakerGeneratorBundle/skeleton/``
* ``resources/MakerGeneratorBundle/skeleton/``

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

<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2020 Serhii Popov
 * This source file is subject to The MIT License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @category Popov
 * @package Popov_MakerGeneratorBundle
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Popov\MakerGeneratorBundle\Listener;

use ReflectionProperty;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;

class ConsoleCommandListener
{
    /**
     * @var Generator
     */
    protected Generator $generator;

    /**
     * ConsoleCommandListener constructor.
     *
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Run with php bin/console make:entity Product --namespace='Acme\Product'
     *
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        // gets the input instance
        $input = $event->getInput();

        // gets the command to be executed
        $command = $event->getCommand();

        if (strpos($command->getName(), 'make:') === 0) {
            // @todo Implement option from generate-bundle
            // @see https://symfony.com/doc/current/bundles/SensioGeneratorBundle/commands/generate_bundle.html#available-options
            if (!$command->getDefinition()->hasOption('namespace')) {
                $command->addOption('namespace', null, InputOption::VALUE_OPTIONAL);
                $input->bind($command->getDefinition());
            }

            $namespace = $input->getOption('namespace');
            if (!$namespace) {
                return;
            }

            $reflection = new ReflectionProperty(Generator::class, 'namespacePrefix');
            $reflection->setAccessible(true);
            $reflection->setValue($this->generator, $namespace);
        }
    }
}

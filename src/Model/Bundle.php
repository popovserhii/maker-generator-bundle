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

namespace Popov\MakerGeneratorBundle\Model;

use Symfony\Component\DependencyInjection\Container;

/**
 * Represents a bundle being built.
 */
class Bundle
{
    private $namespace;

    private $name;

    private $targetDirectory;

    private $configurationFormat;

    private $isShared;

    private $testsDirectory;

    public function __construct($namespace, $name, $targetDirectory, $configurationFormat, $isShared)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->targetDirectory = $targetDirectory;
        $this->configurationFormat = $configurationFormat;
        $this->isShared = $isShared;
        $this->testsDirectory = $this->getTargetDirectory().'/Tests';
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getConfigurationFormat()
    {
        return $this->configurationFormat;
    }

    public function isShared()
    {
        return $this->isShared;
    }

    /**
     * Returns the directory where the bundle will be generated.
     *
     * @return string
     */
    public function getTargetDirectory()
    {
        return rtrim($this->targetDirectory, '/').'/'.trim(strtr($this->namespace, '\\', '/'), '/');
    }

    /**
     * Returns the name of the bundle without the Bundle suffix.
     *
     * @return string
     */
    public function getBasename()
    {
        return substr($this->name, 0, -6);
    }

    /**
     * Returns the dependency injection extension alias for this bundle.
     *
     * @return string
     */
    public function getExtensionAlias()
    {
        return Container::underscore($this->getBasename());
    }

    /**
     * Should a DependencyInjection directory be generated for this bundle?
     *
     * @return bool
     */
    public function shouldGenerateDependencyInjectionDirectory()
    {
        return $this->isShared;
    }

    /**
     * What is the filename for the services.yml/xml file?
     *
     * @return string
     */
    public function getServicesConfigurationFilename()
    {
        if ('yml' === $this->getConfigurationFormat() || 'annotation' === $this->configurationFormat) {
            return 'services.yml';
        } else {
            return 'services.'.$this->getConfigurationFormat();
        }
    }

    /**
     * What is the filename for the routing.yml/xml file?
     *
     * If false, no routing file will be generated
     *
     * @return string|bool
     */
    public function getRoutingConfigurationFilename()
    {
        if ($this->getConfigurationFormat() == 'annotation') {
            return false;
        }

        return 'routing.'.$this->getConfigurationFormat();
    }

    /**
     * Returns the class name of the Bundle class.
     *
     * @return string
     */
    public function getBundleClassName()
    {
        return $this->namespace.'\\'.$this->name;
    }

    public function setTestsDirectory($testsDirectory)
    {
        $this->testsDirectory = $testsDirectory;
    }

    public function getTestsDirectory()
    {
        return $this->testsDirectory;
    }
}

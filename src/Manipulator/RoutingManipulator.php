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

namespace Popov\MakerGeneratorBundle\Manipulator;

use Symfony\Component\DependencyInjection\Container;
use Popov\MakerGeneratorBundle\Generator\DoctrineCrudGenerator;
use Popov\MakerGeneratorBundle\Generator\Generator;
use Symfony\Component\Yaml\Yaml;

/**
 * Changes the PHP code of a YAML routing file.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RoutingManipulator extends Manipulator
{
    private $file;

    /**
     * @param string $file The YAML routing file path
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Adds a routing resource at the top of the existing ones.
     *
     * @param string $bundle
     * @param string $format
     * @param string $prefix
     * @param string $path
     *
     * @return bool Whether the operation succeeded
     *
     * @throws \RuntimeException If bundle is already imported
     */
    public function addResource($bundle, $format, $prefix = '/', $path = 'routing')
    {
        $current = '';
        $code = sprintf("%s:\n", $this->getImportedResourceYamlKey($bundle, $prefix));

        if (file_exists($this->file)) {
            $current = file_get_contents($this->file);

            // Don't add same bundle twice
            if (false !== strpos($current, '@'.$bundle)) {
                throw new \RuntimeException(sprintf('Bundle "%s" is already imported.', $bundle));
            }
        } elseif (!is_dir($dir = dirname($this->file))) {
            Generator::mkdir($dir);
        }

        if ('annotation' == $format) {
            $code .= sprintf("    resource: \"@%s/Controller/\"\n    type:     annotation\n", $bundle);
        } else {
            $code .= sprintf("    resource: \"@%s/Resources/config/%s.%s\"\n", $bundle, $path, $format);
        }
        $code .= sprintf("    prefix:   %s\n", $prefix);
        $code .= "\n";
        $code .= $current;

        if (false === Generator::dump($this->file, $code)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the routing file contains a line for the bundle.
     *
     * @param string $bundle
     *
     * @return bool
     */
    public function hasResourceInAnnotation($bundle)
    {
        if (!file_exists($this->file)) {
            return false;
        }

        $config = Yaml::parse(file_get_contents($this->file));

        $search = sprintf('@%s/Controller/', $bundle);

        foreach ($config as $resource) {
            if (array_key_exists('resource', $resource)) {
                return $resource['resource'] === $search;
            }
        }

        return false;
    }

    /**
     * Adds an annotation controller resource.
     *
     * @param string $bundle
     * @param string $controller
     *
     * @return bool
     */
    public function addAnnotationController($bundle, $controller)
    {
        $current = '';

        if (file_exists($this->file)) {
            $current = file_get_contents($this->file);
        } elseif (!is_dir($dir = dirname($this->file))) {
            mkdir($dir, 0777, true);
        }

        $code = sprintf("%s:\n", Container::underscore(substr($bundle, 0, -6)).'_'.Container::underscore($controller));

        $code .= sprintf("    resource: \"@%s/Controller/%sController.php\"\n    type:     annotation\n", $bundle, $controller);

        $code .= "\n";
        $code .= $current;

        return false !== file_put_contents($this->file, $code);
    }

    public static function getImportedResourceYamlKey($bundle, $prefix)
    {
        $snakeCasedBundleName = Container::underscore(substr($bundle, 0, -6));
        $routePrefix = DoctrineCrudGenerator::getRouteNamePrefix($prefix);

        return sprintf('%s%s%s', $snakeCasedBundleName, '' !== $routePrefix ? '_' : '', $routePrefix);
    }
}

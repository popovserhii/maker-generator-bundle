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

use Symfony\Component\Yaml\Yaml;
use Popov\MakerGeneratorBundle\Generator\Generator;
use Popov\MakerGeneratorBundle\Model\Bundle;

/**
 * Changes the PHP code of a YAML services configuration file.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Ryan Weaver <weaverryan@gmail.com>
 */
class ConfigurationManipulator extends Manipulator
{
    private $file;

    /**
     * @param string $file The YAML configuration file path
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Adds a configuration resource at the top of the existing ones.
     *
     * @param Bundle $bundle
     *
     * @throws \RuntimeException If this process fails for any reason
     */
    public function addResource(Bundle $bundle)
    {
        // @todo Modify this configuration according to the article https://sudonull.com/post/12819-Step-by-step-creating-a-symfony-4-bundle
        // if the config.yml file doesn't exist, don't even try.
        if (!file_exists($this->file)) {
            throw new \RuntimeException(sprintf('The target config file %s does not exist', $this->file));
        }

        $code = $this->getImportCode($bundle);

        $currentContents = file_get_contents($this->file);
        // Don't add same bundle twice
        if (false !== strpos($currentContents, $code)) {
            throw new \RuntimeException(sprintf('The %s configuration file from %s is already imported', $bundle->getServicesConfigurationFilename(), $bundle->getName()));
        }

        // find the "imports" line and add this at the end of that list
        $lastImportedPath = $this->findLastImportedPath($currentContents);
        if (!$lastImportedPath) {
            throw new \RuntimeException(sprintf('Could not find the imports key in %s', $this->file));
        }

        // find imports:
        $importsPosition = strpos($currentContents, 'imports:');
        // find the last import
        $lastImportPosition = strpos($currentContents, $lastImportedPath, $importsPosition);
        // find the line break after the last import
        $targetLinebreakPosition = strpos($currentContents, "\n", $lastImportPosition);

        $newContents = substr($currentContents, 0, $targetLinebreakPosition)."\n".$code.substr($currentContents, $targetLinebreakPosition);

        if (false === Generator::dump($this->file, $newContents)) {
            throw new \RuntimeException(sprintf('Could not write file %s ', $this->file));
        }
    }

    public function getImportCode(Bundle $bundle)
    {
        return sprintf(<<<EOF
    - { resource: "@%s/Resources/config/%s" }
EOF
            ,
            $bundle->getName(),
            $bundle->getServicesConfigurationFilename()
        );
    }

    /**
     * Finds the last imported resource path in the YAML file.
     *
     * @param $yamlContents
     *
     * @return bool|string
     */
    private function findLastImportedPath($yamlContents)
    {
        $data = Yaml::parse($yamlContents);
        if (!isset($data['imports'])) {
            return false;
        }

        // find the last imports entry
        $lastImport = end($data['imports']);
        if (!isset($lastImport['resource'])) {
            return false;
        }

        return $lastImport['resource'];
    }
}

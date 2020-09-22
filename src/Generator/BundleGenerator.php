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

namespace Popov\MakerGeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Popov\MakerGeneratorBundle\Model\Bundle;

/**
 * Generates a bundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class BundleGenerator extends Generator
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generateBundle(Bundle $bundle)
    {
        $dir = $bundle->getTargetDirectory();
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" exists but is a file.', realpath($dir)));
            }
            $files = scandir($dir);
            if ($files != ['.', '..']) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not empty.', realpath($dir)));
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not writable.', realpath($dir)));
            }
        }
        $parameters = [
            'namespace' => $bundle->getNamespace(),
            'bundle' => $bundle->getName(),
            'format' => $bundle->getConfigurationFormat(),
            'bundle_basename' => $bundle->getBasename(),
            'extension_alias' => $bundle->getExtensionAlias(),
        ];
        $this->renderFile('bundle/Bundle.php.twig', $dir . '/' . $bundle->getName() . '.php', $parameters);
        if ($bundle->shouldGenerateDependencyInjectionDirectory()) {
            $this->renderFile('bundle/Extension.php.twig', $dir
                . '/DependencyInjection/'
                . $bundle->getBasename()
                . 'Extension.php', $parameters);
            $this->renderFile('bundle/Configuration.php.twig', $dir
                . '/DependencyInjection/Configuration.php', $parameters);
        }
        $this->renderFile('bundle/DefaultController.php.twig', $dir . '/Controller/DefaultController.php', $parameters);
        $this->renderFile('bundle/DefaultControllerTest.php.twig', $bundle->getTestsDirectory()
            . '/Controller/DefaultControllerTest.php', $parameters);
        $this->renderFile('bundle/index.html.twig.twig', $dir
            . '/Resources/views/Default/index.html.twig', $parameters);
        // render the services.yml/xml file
        $servicesFilename = $bundle->getServicesConfigurationFilename();
        $this->renderFile(
            sprintf('bundle/%s.twig', $servicesFilename),
            $dir . '/Resources/config/' . $servicesFilename, $parameters
        );
        if ($routingFilename = $bundle->getRoutingConfigurationFilename()) {
            $this->renderFile(
                sprintf('bundle/%s.twig', $routingFilename),
                $dir . '/Resources/config/' . $routingFilename, $parameters
            );
        }
    }
}

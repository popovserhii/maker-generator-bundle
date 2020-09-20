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

namespace Popov\MakerGeneratorBundle\Command;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Console\Command\Command;
use Popov\MakerGeneratorBundle\Generator\Generator;
use Popov\MakerGeneratorBundle\Command\Helper\QuestionHelper;

/**
 * Base class for generator commands.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Serhii Popov <popow.serhii@gmail.com>
 */
abstract class GeneratorCommand extends Command
{
    /**
     * @var Generator
     */
    private Generator $generator;

    // only useful for unit tests
    public function setGenerator(Generator $generator)
    {
        $this->generator = $generator;
    }

    abstract protected function createGenerator();

    protected function getGenerator(BundleInterface $bundle = null)
    {
        if (null === $this->generator) {
            $this->generator = $this->createGenerator();
            $this->generator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->generator;
    }

    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $skeletonDirs = [];
        if (isset($bundle) && is_dir($dir = $bundle->getPath() . '/Resources/PopovMakerGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }
        if (is_dir($dir = $this->kernel->getProjectdir() . '/resources/PopovMakerGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }
        $skeletonDirs[] = __DIR__ . '/../Resources/skeleton';
        $skeletonDirs[] = __DIR__ . '/../Resources';

        return $skeletonDirs;
    }

    protected function getQuestionHelper()
    {
        $question = $this->getHelperSet()->get('question');
        if (!$question || get_class($question) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper') {
            $this->getHelperSet()->set($question = new QuestionHelper());
        }

        return $question;
    }

    /**
     * Tries to make a path relative to the project, which prints nicer.
     *
     * @param string $absolutePath
     *
     * @return string
     */
    protected function makePathRelative(string $absolutePath)
    {
        $projectRootDir = $this->params->get('kernel.project_dir');

        return str_replace($projectRootDir . '/', '', realpath($absolutePath) ?: $absolutePath);
    }
}

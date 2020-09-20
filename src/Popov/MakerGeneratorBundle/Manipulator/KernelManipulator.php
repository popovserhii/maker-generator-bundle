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

use Symfony\Component\HttpKernel\KernelInterface;
use Popov\MakerGeneratorBundle\Generator\Generator;

/**
 * Changes the PHP code of a Kernel.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class KernelManipulator extends Manipulator
{
    protected $kernel;
    protected $reflected;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->reflected = new \ReflectionObject($kernel);
    }

    /**
     * Adds a bundle at the end of the existing ones.
     *
     * @param string $bundle The bundle class name
     *
     * @return bool Whether the operation succeeded
     *
     * @throws \RuntimeException If bundle is already defined
     */
    public function addBundle($bundle)
    {
        if (!$this->getFilename()) {
            return false;
        }

        //$src = file($this->getFilename());
        $configPath = '/config/bundles.php';
        $src = file($this->kernel->getProjectDir() . $configPath);
        //$method = $this->reflected->getMethod('registerBundles');
        //$lines = array_slice($src, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);

        // @todo Get only return[...] block from $src
        $lines = $src;

        // Don't add same bundle twice
        if (false !== strpos(implode('', $lines), $bundle)) {
            throw new \RuntimeException(sprintf('Bundle "%s" is already defined in "%s".', $bundle, $configPath));
        }

        //$this->setCode(token_get_all('<?php '.implode('', $lines)), $method->getStartLine());
        $this->setCode(token_get_all('<?php '.implode('', $lines)));

        while ($token = $this->next()) {
            // $bundles
            if (T_RETURN !== $token[0]) {
                continue;
            }

            // =
            //$this->next();

            // array start with traditional or short syntax
            $token = $this->next();
            if (T_ARRAY !== $token[0] && '[' !== $this->value($token)) {
                return false;
            }

            // add the bundle at the end of the array
            while ($token = $this->next()) {
                // look for ); or ];
                if (')' !== $this->value($token) && ']' !== $this->value($token)) {
                    continue;
                }

                if (';' !== $this->value($this->peek())) {
                    continue;
                }

                // ;
                $this->next();

                $leadingContent = implode('', array_slice($src, 0, $this->line + 1));

                // trim semicolon
                $leadingContent = rtrim(rtrim($leadingContent), ';');

                // We want to match ) & ]
                $closingSymbolRegex = '#(\)|])$#';

                // get closing symbol used
                preg_match($closingSymbolRegex, $leadingContent, $matches);
                $closingSymbol = $matches[0];

                // remove last close parentheses
                $leadingContent = rtrim(preg_replace($closingSymbolRegex, '', rtrim($leadingContent)));

                if ('(' !== substr($leadingContent, -1) && '[' !== substr($leadingContent, -1)) {
                    // end of leading content is not open parentheses or bracket, then assume that array contains at least one element
                    $leadingContent = rtrim($leadingContent, ',').',';
                }

                $lines = array_merge(
                    array($leadingContent, "\n"),
                    array(str_repeat(' ', 4), sprintf("%s::class => ['all' => true],", $bundle), "\n"),
                    array(str_repeat(' ', 0), $closingSymbol.';', "\n"),
                    array_slice($src, $this->line + 1)
                );

                Generator::dump($this->kernel->getProjectDir() . $configPath, implode('', $lines));

                return true;
            }
        }
    }

    public function getFilename()
    {
        return $this->reflected->getFileName();
    }

    public function getBundlesConfigPath()
    {
        return $this->kernel->getProjectDir() . '/config/bundles.php';
    }
}

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

/**
 * Changes the PHP code of a Kernel.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Manipulator
{
    protected $tokens;
    protected $line;

    /**
     * Sets the code to manipulate.
     *
     * @param array $tokens An array of PHP tokens
     * @param int   $line   The start line of the code
     */
    protected function setCode(array $tokens, $line = 0)
    {
        $this->tokens = $tokens;
        $this->line = $line;
    }

    /**
     * Gets the next token.
     *
     * @return string|null
     */
    protected function next()
    {
        while ($token = array_shift($this->tokens)) {
            $this->line += substr_count($this->value($token), "\n");

            if (is_array($token) && in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT))) {
                continue;
            }

            return $token;
        }
    }

    /**
     * Peeks the next token.
     *
     * @param int $nb
     *
     * @return string|null
     */
    protected function peek($nb = 1)
    {
        $i = 0;
        $tokens = $this->tokens;
        while ($token = array_shift($tokens)) {
            if (is_array($token) && in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT))) {
                continue;
            }

            ++$i;
            if ($i == $nb) {
                return $token;
            }
        }
    }

    /**
     * Gets the value of a token.
     *
     * @param string|string[] $token The token value
     *
     * @return string
     */
    protected function value($token)
    {
        return is_array($token) ? $token[1] : $token;
    }
}

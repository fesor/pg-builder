<?php
/**
 * Query builder for PostgreSQL backed by a query parser
 *
 * LICENSE
 *
 * This source file is subject to BSD 2-Clause License that is bundled
 * with this package in the file LICENSE and available at the URL
 * https://raw.githubusercontent.com/sad-spirit/pg-builder/master/LICENSE
 *
 * @package   sad_spirit\pg_builder
 * @copyright 2014-2017 Alexey Borzov
 * @author    Alexey Borzov <avb@php.net>
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD 2-Clause license
 * @link      https://github.com/sad-spirit/pg-builder
 */

namespace sad_spirit\pg_builder\nodes;

use sad_spirit\pg_builder\Node,
    sad_spirit\pg_builder\Token,
    sad_spirit\pg_builder\exceptions\InvalidArgumentException,
    sad_spirit\pg_builder\TreeWalker;

/**
 * Represents a named ':foo' or positional '$1' query parameter
 *
 * @property-read integer $type  Either Token::TYPE_POSITIONAL_PARAM or TYPE_NAMED_PARAM
 * @property-read string  $value Parameter number or name
 */
class Parameter extends Node implements ScalarExpression
{
    public function __construct($tokenOrName)
    {
        if ($tokenOrName instanceof Token) {
            if (0 === (Token::TYPE_PARAMETER & $tokenOrName->getType())) {
                throw new InvalidArgumentException(sprintf(
                    '%s expects a parameter token, %s given',
                    __CLASS__, Token::typeToString($tokenOrName->getType())
                ));
            }
            $this->props['type']  = $tokenOrName->getType();
            $this->props['value'] = $tokenOrName->getValue();

        } elseif (ctype_digit((string)$tokenOrName)) {
            $this->props['type']  = Token::TYPE_POSITIONAL_PARAM;
            $this->props['value'] = $tokenOrName;

        } elseif (is_string($tokenOrName)) {
            $this->props['type']  = Token::TYPE_NAMED_PARAM;
            $this->props['value'] = $tokenOrName;

        } else {
            throw new InvalidArgumentException(sprintf(
                '%s requires a Token instance or parameter number / name, %s given',
                __CLASS__, is_object($tokenOrName) ? 'object(' . get_class($tokenOrName) . ')'
                           : gettype($tokenOrName)
            ));

        }
    }

    public function dispatch(TreeWalker $walker)
    {
        return $walker->walkParameter($this);
    }

    /**
     * Checks in base setParentNode() are redundant as this can only be a leaf node
     *
     * @param Node $parent
     */
    protected function setParentNode(Node $parent = null)
    {
        if ($parent && $this->parentNode && $parent !== $this->parentNode) {
            $this->parentNode->removeChild($this);
        }
        $this->parentNode = $parent;
    }
}
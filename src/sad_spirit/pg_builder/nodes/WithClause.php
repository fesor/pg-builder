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

use sad_spirit\pg_builder\nodes\lists\NonAssociativeList,
    sad_spirit\pg_builder\exceptions\InvalidArgumentException,
    sad_spirit\pg_builder\TreeWalker,
    sad_spirit\pg_builder\Parseable,
    sad_spirit\pg_builder\ElementParseable,
    sad_spirit\pg_builder\Parser;

/**
 * WITH clause containing common table expressions
 *
 * @property bool $recursive
 */
class WithClause extends NonAssociativeList implements Parseable, ElementParseable
{
    public function __construct($ctes, $recursive = false)
    {
        parent::__construct($ctes);
        $this->setRecursive($recursive);
    }

    protected function normalizeElement(&$offset, &$value)
    {
        parent::normalizeElement($offset, $value);

        if (!($value instanceof CommonTableExpression)) {
            throw new InvalidArgumentException(sprintf(
                '%s can contain only instances of CommonTableExpression, %s given',
                __CLASS__, is_object($value) ? 'object(' . get_class($value) . ')' : gettype($value)
            ));
        }
    }

    public function dispatch(TreeWalker $walker)
    {
        return $walker->walkWithClause($this);
    }

    public function createElementFromString($sql)
    {
        if (!($parser = $this->getParser())) {
            throw new InvalidArgumentException("Passed a string as a list element without a Parser available");
        }
        return $parser->parseCommonTableExpression($sql);
    }

    public static function createFromString(Parser $parser, $sql)
    {
        return $parser->parseWithClause($sql);
    }

    /**
     * When merging two WITH clauses also set the 'recursive' property of the target one
     *
     * @param array|string|\Traversable $array
     * @param string                    $method
     */
    protected function normalizeArray(&$array, $method)
    {
        parent::normalizeArray($array, $method);
        if ($array instanceof WithClause && $array->recursive) {
            $this->props['recursive'] = true;
        }
    }

    public function setRecursive($recursive)
    {
        $this->props['recursive'] = (bool)$recursive;
    }
}

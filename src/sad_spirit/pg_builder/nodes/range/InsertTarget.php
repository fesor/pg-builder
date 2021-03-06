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

namespace sad_spirit\pg_builder\nodes\range;

use sad_spirit\pg_builder\Node,
    sad_spirit\pg_builder\nodes\Identifier,
    sad_spirit\pg_builder\nodes\QualifiedName,
    sad_spirit\pg_builder\TreeWalker;


/**
 * AST node for target of INSERT statement (insert_target production in gram.y)
 *
 * Reusing RelationReference for targets of INSERT/UPDATE/DELETE is not quite correct as the latter cannot
 * have column aliases and cannot participate in JOINs
 *
 * @property-read QualifiedName   $relation
 * @property-read Identifier|null $alias
 */
class InsertTarget extends Node
{
    public function __construct(QualifiedName $relation, Identifier $alias = null)
    {
        $this->setNamedProperty('relation', $relation);
        $this->setNamedProperty('alias', $alias);
    }

    public function dispatch(TreeWalker $walker)
    {
        return $walker->walkInsertTarget($this);
    }

    /**
     * Checks in base setParentNode() are redundant as this can only contain QualifiedName/Identifier instances
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
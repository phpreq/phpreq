<?php

/**
 *  PHPReq - determine requirements for your PHP app
 *  Copyright (C) 2014-present Stuart Herbert
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Stuart Herbert <stuart@stuartherbert.com>
 * @copyright (c) 2014-present Stuart Herbert
 */

namespace PHPReq\Scanner;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

ini_set('xdebug.var_display_max_children', 20000);
ini_set('xdebug.var_display_max_depth', 100);

class ExpressionExpander extends NodeVisitorAbstract
{
	private $phpReqNodesToExpand = array(
		"PhpParser\\Node\\Expr\\MethodCall" => "expandMethodCall",
		"PhpParser\\Node\\Expr\\New_"       => "expandNew",
		"PhpParser\\Node\\Expr\\Assign"     => "trackVar",
	);

	private $trackedVars = array();

	public function leaveNode(Node $node)
	{
		$className = get_class($node);
		echo "???: $className ";
		//var_dump($node);

		// is this a node that we want to expand?
		if (!isset($this->phpReqNodesToExpand[$className])) {
			// no, it is not
			echo "IGN" . PHP_EOL;
			return;
		}
		echo "INS" . PHP_EOL;

		// at this point, we are looking at a node that we need to expand
		//
		// we now need to work out exactly what we are looking at, and
		// expand it accordingly

		call_user_func_array(array($this, $this->phpReqNodesToExpand[$className]), array($node));

		return $node;
	}

	public function expandMethodCall(Node $node)
	{

	}

	public function expandNew(Node $node)
	{
		// special case - nothing to expand
		if (is_string($node->class)) {
			$node->fqName = $node->class;
		}
		else if ($node->class instanceof \PhpParser\Node\Name) {
			$node->fqName = $node->class->toString();
		}

		// at this point, we're hoping to get lucky
		//
		// this IF statement will detect a variable that is holding
		// the name of the class
		if ($node->class instanceof \PhpParser\Node\Expr\Variable) {
			$name = $node->class->name;
			if (!is_string($name)) {
				$name = $name->toString();
			}

			// do we know what this variable contains?
			if (isset($this->trackedVars[$name])) {
				$node->fqName = $this->trackedVars[$name];
			}
		}
	}

	public function trackVar(Node $node)
	{
		$varClass  = get_class($node->var);
		$exprClass = get_class($node->expr);

		// do we have a variable?
		switch ($varClass)
		{
			case 'PhpParser\Node\Expr\Variable':
				$name = $node->var->name;
				if (!is_string($name)) {
					$name = $node->var->name->toString();
				}

				// do we have a nice, simple scalar?
				switch ($exprClass)
				{
					case 'PhpParser\Node\Scalar\String':
						$this->trackedVars[$name] = $node->expr->value;
				}
		}
	}
}
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
use PhpParser\Node\Stmt;

class NodeInspector extends NodeVisitorAbstract
{
	private $discovered;

	public function initInspector()
	{
		$this->discovered = array();
	}

	public function leaveNode(Node $node)
	{
		$className = get_class($node);

		switch($className)
		{
			// classes
			case "PhpParser\\Node\\Expr\\MethodCall":
				// fqName is set by our ExpressionExpander
				if (isset($node->fqName)) {
					$name = $node->fqName;
					$this->discovered["methods_called"][$name] = $name;
				}
				break;

			case "PhpParser\\Node\\Expr\\New_":
				// fqName is set by our ExpressionExpander
				if (isset($node->fqName)) {
					$name = $node->fqName;
					$this->discovered["classes_used"][$name] = $name;
				}
				break;

			// functions
			case "PhpParser\\Node\\Expr\\FuncCall":
				if ($node->name instanceof \PhpParser\Node\Name) {
					$name = $node->name->toString();
					$this->discovered["functions_called"][$name] = $name;
				}
				break;
			case "PhpParser\\Node\\Stmt\\Function_":
				$name = $node->namespacedName->toString();
				$this->discovered["functions_declared"][$name] = $name;
				break;
		}
	}

	public function getDiscovered()
	{
		return $this->discovered;
	}
}
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

use PHPUnit_Framework_TestCase;

use PhpParser\Parser;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class NodeInspectorTest extends PHPUnit_Framework_TestCase
{
	protected function loadFile($name)
	{
		$filename = __DIR__ . "/data/$name";
		return file_get_contents($filename);
	}

    protected function traverseFile($name, $debug = false)
    {
        // go and get the file
        $parser = new Parser(new Emulative);
        $parseTree = $parser->parse($this->loadFile($name));

        // this is what we're going to do to our parse tree
        $treeTrav = new NodeTraverser();
        $treeTrav->addVisitor(new NameResolver);
        $ee = new ExpressionExpander();
        if ($debug) {
        	$ee->enableDebug();
        }
        $treeTrav->addVisitor($ee);

        // let's see what's in there!
        $parseTree = $treeTrav->traverse($parseTree);

        // all done
        return $parseTree;
    }

    protected function extractFromTree($parseTree)
    {
    	// we're going to use our inspector to discover what
    	// is in the tree that we are parsing
        $treeTrav = new NodeTraverser();
        $inspector = new NodeInspector();
        $inspector->initInspector();
        $treeTrav->addVisitor($inspector);

        // let's see what's in there!
        $parseTree = $treeTrav->traverse($parseTree);

        // all done
        return $inspector->getDiscovered();
    }

	/**
	 * @covers PHPReq\Scanner\NodeInspector::leaveNode
	 */
	public function testCanDetectCallingGlobalFunction()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"functions_called" => array (
				"ini_get" => "ini_get"
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("calls_global_function.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\NodeInspector::leaveNode
	 */
	public function testCanDetectCallingStaticMethodGlobalClassname()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				"stdClass" => "stdClass"
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("calls_static_method_global_classname.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

}
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

class ExpressionExpanderTest extends PHPUnit_Framework_TestCase
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
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandNew
	 */
	public function testCanDetectGlobalClassnames()
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

		$tree = $this->traverseFile("instantiates_global_classname.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandNew
	 */
	public function testCanDetectNamespacedClassnameImplicitAlias()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				'PHPReq\Scanner\NodeInspector' => 'PHPReq\Scanner\NodeInspector'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("instantiates_namespaced_classname_implicit_alias.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandNew
	 */
	public function testCanDetectNamespacedClassnameExplicitAlias()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				'PHPReq\Scanner\NodeInspector' => 'PHPReq\Scanner\NodeInspector'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("instantiates_namespaced_classname_explicit_alias.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandNew
	 */
	public function testCanDetectNamespacedClassnameWithNoImport()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				'PHPReq\Scanner\NodeInspector' => 'PHPReq\Scanner\NodeInspector'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("instantiates_namespaced_classname_no_import.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandNew
	 */
	public function testCanDetectNamespacedClassnameNameInVariable()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				'PHPReq\Scanner\NodeInspector' => 'PHPReq\Scanner\NodeInspector'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("instantiates_namespaced_classname_name_in_variable.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandNew
	 */
	public function testCanDetectExtendsGlobalClassname()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				'stdClass' => 'stdClass'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("extends_global_classname.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandNew
	 */
	public function testCanDetectExtendsNamespacedClassnameNoImport()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				'PHPReq\Scanner\NodeInspector' => 'PHPReq\Scanner\NodeInspector'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("extends_namespaced_classname_no_import.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandNew
	 */
	public function testCanDetectExtendsNamespacedClassnameImplicitAlias()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				'PHPReq\Scanner\NodeInspector' => 'PHPReq\Scanner\NodeInspector'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("extends_namespaced_classname_implicit_alias.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandNew
	 */
	public function testCanDetectExtendsNamespacedClassnameExplicitAlias()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				'PHPReq\Scanner\NodeInspector' => 'PHPReq\Scanner\NodeInspector'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("extends_namespaced_classname_explicit_alias.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandNew
	 */
	public function testCanDetectImplementsGlobalClassname()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"interfaces_used" => array (
				'Harold' => 'Harold'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("implements_global_interface.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}


	/**
	 * @covers PHPReq\Scanner\NodeInspector::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandStaticCall
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

	/**
	 * @covers PHPReq\Scanner\NodeInspector::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandStaticCall
	 */
	public function testCanDetectCallingStaticMethodNamespacedClassnameExplicitAlias()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				'PHPReq\Scanner\NodeInspector' => 'PHPReq\Scanner\NodeInspector'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("calls_static_method_namespaced_classname_explicit_alias.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\NodeInspector::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandStaticCall
	 */
	public function testCanDetectCallingStaticMethodNamespacedClassnameImplicitAlias()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				'PHPReq\Scanner\NodeInspector' => 'PHPReq\Scanner\NodeInspector'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("calls_static_method_namespaced_classname_implicit_alias.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers PHPReq\Scanner\NodeInspector::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::leaveNode
	 * @covers PHPReq\Scanner\ExpressionExpander::expandStaticCall
	 */
	public function testCanDetectCallingStaticMethodNamespacedClassnameNoImport()
	{
	    // ----------------------------------------------------------------
	    // setup your test

		$expected = array(
			"classes_used" => array (
				'PHPReq\Scanner\NodeInspector' => 'PHPReq\Scanner\NodeInspector'
			),
		);

	    // ----------------------------------------------------------------
	    // perform the change

		$tree = $this->traverseFile("calls_static_method_namespaced_classname_no_import.php");

	    // ----------------------------------------------------------------
	    // test the results

		$actual = $this->extractFromTree($tree);
		$this->assertEquals($expected, $actual);
	}

}
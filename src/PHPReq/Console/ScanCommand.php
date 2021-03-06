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

namespace PHPReq\Console;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

use PhpParser\Parser;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

use PHPReq\Scanner\ExpressionExpander;
use PHPReq\Scanner\NodeInspector;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

ini_set('xdebug.max_nesting_level', 2000);
ini_set('memory_limit', -1);

class ScanCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('scan')
            ->setDescription('scan a codebase to discover requirements')
            ->addArgument(
                '<path>',
                InputArgument::REQUIRED,
                'where is the codebase to scan?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // make sure we're looking at a real project
        $pathToScan = $input->getArgument('<path>');
        $this->validatePath($pathToScan);

        $filenames = $this->findPhpFilesInFolder($pathToScan);
        if (count($filenames) == 0) {
            $output->writeln("<error>no PHP files found in '{$pathToScan}'");
            exit(1);
        }

        // we'll show a progress bar to our user
        $output->writeln("<info>scanning PHP files ... please wait ...</info>");
        $progress = new ProgressBar($output, count($filenames));
        $progress->start();

        // extract a list of what's in the files
        $discovered = array();
        foreach ($filenames as $filename) {
            $this->mergeDiscovered($this->parseFile($filename), $discovered);
            $progress->advance();
        }

        // completed scanning
        $progress->finish();
        $output->writeln('');

        // now, what did we find?
        foreach ($discovered as $type => $data) {
            echo "Found " . count($data) . " " . $type . PHP_EOL;
        }
    }

    protected function validatePath($pathToScan)
    {
        // does the path to scan exist?
        if (!is_dir($pathToScan)) {
            $output->writeln("<error>path '{$pathToScan}' not found or is not a folder</error>");
            exit(1);
        }
    }

    protected function findPhpFilesInFolder($folder)
    {
        // use the SPL to do the heavy lifting
        $dirIter = new RecursiveDirectoryIterator($folder);
        $recIter = new RecursiveIteratorIterator($dirIter);
        $regIter = new RegexIterator($recIter, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        // what happened?
        $filenames = [];
        foreach ($regIter as $match) {
            $filenames[] = $match[0];
        }

        // let's get the list into some semblance of order
        sort($filenames);

        // all done
        return $filenames;
    }

    protected function parseFile($filename)
    {
        // go and get the file
        $parser = new Parser(new Emulative);
        $parseTree = $parser->parse(file_get_contents($filename));

        // these helpers will change the parse tree, to
        // make it a fuck-tonne easier to inspect later on
        //
        // we assume that the order matters here :)
        $treeTrav = new NodeTraverser();
        $treeTrav->addVisitor(new NameResolver);
        $treeTrav->addVisitor(new ExpressionExpander);

        // finally, we add our inspector, to make sense of
        // everything we've seen / done to the tree
        $inspector = new NodeInspector();
        $inspector->initInspector();
        $treeTrav->addVisitor($inspector);

        // let's see what's in there!
        $parseTree = $treeTrav->traverse($parseTree);

        // all done
        return $inspector->getDiscovered();
    }

    protected function mergeDiscovered($input, &$output)
    {
        foreach ($input as $type => $list) {
            foreach ($list as $name) {
                if (!isset($output[$type])) {
                    $output[$type] = array();
                }
                $output[$type][$name] = $name;
            }
        }
    }
}
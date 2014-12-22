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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
    }

    protected function validatePath($pathToScan)
    {
        // does the path to scan exist?
        if (!is_dir($pathToScan)) {
            $output->writeln("<error>path '{$pathToScan}' not found or is not a folder</error>");
            exit(1);
        }

        // does the path contain a composer.json file?
        $composerFilename = $this->getComposerFilename($pathToScan);

        if (!file_exists($composerFilename)) {
            $output->writeln("<error>file '{$composerFilename}' not found</error>");
            exit(1);
        }
    }

    protected function getComposerFilename($pathToScan)
    {
        return realpath($pathToScan . '/composer.json');
    }
}
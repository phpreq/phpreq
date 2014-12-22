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

// we are part of the UI
namespace PHPReq\Console;

// we use Symfony's console to communicate with our user
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
	private $url;

	/**
	 * Returns the URL where the user can learn more about this app
	 *
	 * @return string The URL to display
	 */
	public function getUrl()
	{
		if (isset($this->url)) {
			return $this->url;
		}

		return 'UNKNOWN';
	}

	/**
	 * sets the URL where the user can learn more about this app
	 *
	 * @param string $url
	 *        the URL to display to the user
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}

    /**
     * Returns the long version of the application.
     *
     * @return string The long application version
     *
     * @api
     */
    public function getLongVersion()
    {
    	// our return value
    	$retval = "";

    	// first part to display
        if ('UNKNOWN' !== $this->getName() && 'UNKNOWN' !== $this->getVersion()) {
            $retval = sprintf('<info>%s</info> v<comment>%s</comment>', $this->getName(), $this->getVersion());
        }
        else {
        	$retval = '<info>Console Tool</info>';
        }

        // second part to display
        if ('UNKNOWN' !== $this->getUrl()) {
        	$retval .= ' - ' . sprintf("<info>%s</info>", $this->getUrl());
        }

        // all done
        return $retval;
    }

}
# phpreq

What are the PHP requirements for your code?

## What Does It Do?

If someone wants to use your code, but isn't familiar with PHP, they often don't know how to setup their copy of PHP so that your code runs correctly.  `phpreq` is the tool that plugs this gap.

You run `phpreq` against your code. It:

1. parses your PHP code (done)
1. works out which PHP extensions your code needs (in progress)
1. generates a class that checks for the PHP extensions your code needs (tbd)
1. provides instructions on how to setup PHP on different operating systems for your code (tbd)

The generated class will be public domain, so that you can incorporate it into any PHP project, including commercial projects.

## How To Install

    composer install

## How To Run

This will scan the PHPReq codebase (and the `vendor` folder too):

    bin/phpreq scan .

## How To Test

	vendor/bin/phpunit

## How To Contribute

Fork on Github, and then:

	git clone git@github.com:<your-username>/phpreq.git
	cd phpreq
	git checkout -b feature/<your-feature-name>
	git branch --track origin/feature/<your-feature-name>

Work on your changes (please include tests!), and when ready, send a Github pull request against the `develop` branch of the `phpreq/phpreq` project.
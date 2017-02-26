Installation
============

### Get the package using composer

Add the SilexStaticPageGenerator by running this command from the terminal at the root of
your Silex project:

```bash
php composer.phar require glavweb/silex-static-page-generator
```

### Register the command in the console file:

```bash
#!/usr/bin/env php
<?php

set_time_limit(0);

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;
use Glavweb\SilexStaticPageGenerator\Command\GenerateStaticPagesCommand;

$input = new ArgvInput();
$env = $input->getParameterOption(['--env', '-e'], 'dev');
$debug = !$input->hasParameterOption(['--no-debug', '']) && $env !== 'prod';

if ($debug) {
    Debug::enable();
}

$app = new Application();
$app->prepare('prod');

$console = new ConsoleApplication();

// ... register commands

$baseUrl = ''; // define the project URL in your the config file
$webDir  = realpath(__DIR__ . '/../web');

$console->add(new GenerateStaticPagesCommand(
    $app['routes'],
    $app['controllers'],
    $app['url_generator'],
    $baseUrl,           // Base URL, as example: http://my_project.com
    $webDir . '/static' // The place where will generate static pages
));

$console->run($input);
```

Note: You need define "$baseUrl" and "$webDir". 

Usage
=====

Run command "generate:static-pages":

```bash
php bin/console generate:static-pages
```

will be generated pages in folder defined in the console file ($webDir . '/static'). 

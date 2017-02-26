<?php

/*
 * This file is part of the GLAVWEB.cms SilexCmsCompositeObject package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\SilexStaticPageGenerator\Command;

use Silex\ControllerCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class GenerateStaticPages
 *
 * @package Glavweb\SilexStaticPageGenerator
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class GenerateStaticPages extends Command
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var ControllerCollection
     */
    private $controllerCollection;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $staticBaseDir;

    /**
     * GenerateStaticPages constructor.
     *
     * @param RouteCollection      $routeCollection
     * @param ControllerCollection $controllerCollection
     * @param UrlGenerator         $urlGenerator
     * @param string               $baseUrl
     * @param string               $staticBaseDir
     */
    public function __construct(RouteCollection $routeCollection, ControllerCollection $controllerCollection, UrlGenerator $urlGenerator, $baseUrl, $staticBaseDir)
    {
        parent::__construct(null);

        $this->routeCollection      = $routeCollection;
        $this->controllerCollection = $controllerCollection;
        $this->urlGenerator         = $urlGenerator;
        $this->baseUrl              = $baseUrl;
        $this->staticBaseDir        = $staticBaseDir;
    }

    /**
     * Configuring the Command
     */
    protected function configure()
    {
        $this
            ->setName('generate:static-pages')
            ->setDescription('Creates HTML pages in web folder.')
            ->setHelp("This command create HTML pages in web folder.")
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        $this->routeCollection->addCollection($this->controllerCollection->flush());

        // clean static dir
        $fs->remove(glob($this->staticBaseDir . '/*'));

        /** @var \Symfony\Component\Routing\RouteCollection $routes */
        foreach ($this->routeCollection->all() as $routeName => $route) {
            if (strpos($route->getPath(), '{') !== false) {
                continue;
            }

            $path = $this->urlGenerator->generate($routeName, [], UrlGeneratorInterface::ABSOLUTE_PATH);
            $url  = $this->baseUrl . $path;

            $pageContent   = file_get_contents($url);
            $staticFile    = $this->staticBaseDir . $this->convertLastSlashToIndex($path) . '.html';
            $staticFileDir = dirname($staticFile);

            if (!is_dir($staticFileDir)) {
                try {

                    $fs->mkdir($staticFileDir);

                } catch (IOExceptionInterface $e) {
                    $output->writeln('An error occurred while creating your directory at ' . $e->getPath());

                    return 1;
                }
            }

            file_put_contents($staticFile, $pageContent);
        }

        $output->writeln('Generate static pages was successful!.');

        return 0;
    }

    /**
     * @param string $path
     * @return string
     */
    private function convertLastSlashToIndex($path)
    {
        if (substr($path, -1) == '/') {
            return $path . 'index';
        }

        return $path;
    }
}
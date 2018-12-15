<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

class AssetExtension extends \Twig_Extension
{
    private $projectpath;

    public function __construct($projectpath)
    {
        $this->projectpath = $projectpath;
    }

    public function getFunctions()
    {
        return [new \Twig_SimpleFunction('asset_exists', [$this, 'assetExists'], ['is_safe' => ['html']])];
    }

    public function assetExists($path)
    {
        $publicRoot = realpath($this->projectpath.'/public/').DIRECTORY_SEPARATOR;
        $toCheck = $publicRoot.$path;

        // check if the file exists
        if (!is_file($toCheck)) {
            return false;
        }

        // check if file is well contained in web/ directory (prevents ../ in paths)
        if (0 !== strncmp($publicRoot, $toCheck, strlen($publicRoot))) {
            return false;
        }

        return true;
    }

    public function getName()
    {
        return 'asset_exists';
    }
}

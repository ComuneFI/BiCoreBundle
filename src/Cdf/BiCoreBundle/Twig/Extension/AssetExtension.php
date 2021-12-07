<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetExtension extends AbstractExtension
{
    private string $projectpath;

    public function __construct(string $projectpath)
    {
        $this->projectpath = $projectpath;
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('asset_exists', [$this, 'assetExists'], ['is_safe' => ['html']])];
    }

    public function assetExists(string $path): bool
    {
        $publicRoot = realpath($this->projectpath . '/public/') . DIRECTORY_SEPARATOR;
        $toCheck = $publicRoot . $path;

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
}

<?php

declare(strict_types=1);

namespace Cdf\BiCoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetExtension extends AbstractExtension
{
    public function __construct(private readonly string $projectpath)
    {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('asset_exists', $this->assetExists(...), ['is_safe' => ['html']])];
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
        return 0 === strncmp($publicRoot, $toCheck, strlen($publicRoot));
    }
}

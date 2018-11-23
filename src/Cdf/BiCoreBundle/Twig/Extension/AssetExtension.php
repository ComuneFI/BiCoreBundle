<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AssetExtension extends \Twig_Extension
{

    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getFunctions()
    {
        return [new \Twig_SimpleFunction('asset_exists', [$this, 'assetExists'], ['is_safe' => ['html']])];
    }

    public function assetExists($path)
    {
        $publicRoot = realpath($this->kernel->getRootDir() . '/../public/') . DIRECTORY_SEPARATOR;
        $toCheck = realpath($publicRoot . $path);
        
        // check if the file exists
        if (!is_file($toCheck)) {
            return false;
        }

        // check if file is well contained in web/ directory (prevents ../ in paths)
        if (strncmp($publicRoot, $toCheck, strlen($publicRoot)) !== 0) {
            return false;
        }

        return true;
    }

    public function getName()
    {
        return 'asset_exists';
    }
}

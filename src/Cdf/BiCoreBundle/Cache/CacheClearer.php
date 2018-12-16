<?php

namespace Cdf\BiCoreBundle\Cache;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;

class CacheClearer implements CacheClearerInterface
{
    public function clear($cacheDirectory)
    {
        //Qui si possono mettere operazioni da fare in caso di clear cache
        $cache = new FilesystemCache();
        if ($cache->has('git_tag')) {
            $cache->delete('git_tag');
            $cache->clear();
        }
    }
}

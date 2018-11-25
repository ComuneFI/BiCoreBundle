<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class UtilitaExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('json_decode', array($this, 'jsonDecode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('db2data', array($this, 'getDb2data', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('remote_file_exists', array($this, 'remoteFileExists', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('serviceExists', array($this, 'serviceExists')),
            new \Twig_SimpleFunction('getParameter', array($this, 'getParameter')),
        );
    }
    
    public function getParameter($parameter)
    {
        if ($this->container->hasParameter($parameter)) {
            return $this->container->getParameter($parameter);
        } else {
            return '';
        }
    }

    public function serviceExists($service)
    {
        return $this->container->has($service);
    }
    
    public function getDb2data($giorno)
    {
        // highlight_string highlights php code only if '<?php' tag is present.

        return FiUtilita::db2data($giorno, true);
    }
    public function jsonDecode($string)
    {
        return json_decode($string);
    }
    /**
     * @param string $url
     *
     * @return bool
     */
    public function remoteFileExists($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $status === 200 ? true : false;
    }
}

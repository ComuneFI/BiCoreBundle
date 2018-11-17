<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Controller\FiUtilita;

class UtilitaExtension extends \Twig_Extension
{

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('db2data', array($this, 'getDb2data', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('remote_file_exists', array($this, 'remoteFileExists', 'is_safe' => array('html'))),
        );
    }

    public function getDb2data($giorno)
    {
        // highlight_string highlights php code only if '<?php' tag is present.

        return FiUtilita::db2data($giorno, true);
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

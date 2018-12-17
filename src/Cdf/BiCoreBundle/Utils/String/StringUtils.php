<?php

namespace Cdf\BiCoreBundle\Utils\String;

class StringUtils
{
    /**
     * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName).
     *
     * @param array $parametri
     *
     * @return string $str translated into camel caps
     */
    public static function toCamelCase($parametri = array())
    {
        $str = $parametri['str'];
        $capitalise_first_char = isset($parametri['primamaiuscola']) ? $parametri['primamaiuscola'] : false;

        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }
        $func = function ($matches) {
            return strtoupper($matches[1]);
        };

        return preg_replace_callback('/_([a-z])/', $func, $str);
    }
}

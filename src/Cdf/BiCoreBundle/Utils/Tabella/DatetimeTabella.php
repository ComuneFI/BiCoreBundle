<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

class DatetimeTabella extends \DateTime implements \JsonSerializable
{

    public function jsonSerialize(): mixed
    {
        return $this->format('c');
    }
}

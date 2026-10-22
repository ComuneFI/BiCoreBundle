<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

class DatetimeTabella extends \DateTime implements \JsonSerializable
{

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->format('c');
    }
}

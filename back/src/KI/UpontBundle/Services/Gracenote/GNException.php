<?php

namespace KI\UpontBundle\Services\Gracenote;

// Extend normal PHP exceptions by includes an additional information field we can utilize.
class GNException extends \Exception
{
    private $extInfo; // Additional information on the exception.

    public function __construct($code = 0, $extInfo = '')
    {
        parent::__construct(GNError::getMessage($code), $code);
        $this->extInfo = $extInfo;
        echo('exception: code=' . $code . ', message=' . GNError::getMessage($code) . ', ext=' . $extInfo . '\n');
    }

    public function getExtraInfo() { return $this->extInfo; }
}

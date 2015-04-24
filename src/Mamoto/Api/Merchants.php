<?php
namespace Mamoto\Api;

class Merchants extends AbstractApi
{

    private $_url = "/merchants";

    public function __construct($config = null)
    {
        $this->url = $this->_url;
        
        parent::__construct($config);
    }
}

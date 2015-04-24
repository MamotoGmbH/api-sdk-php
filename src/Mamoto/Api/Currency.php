<?php
namespace Mamoto\Api;

class Currency extends AbstractApi
{

    private $_url = "/currency";

    public function __construct($config = null)
    {
        $this->url = $this->_url;
        
        parent::__construct($config);
    }
}

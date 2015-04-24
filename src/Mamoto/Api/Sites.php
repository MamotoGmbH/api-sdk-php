<?php
namespace Mamoto\Api;

class Sites extends AbstractApi
{

    private $_url = "/sites";

    public function __construct($config = null)
    {
        $this->url = $this->_url;
        
        parent::__construct($config);
    }
}

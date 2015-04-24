<?php
namespace Mamoto\Api;

use Exception;

class ClientsReports extends AbstractApi
{

    private $_url = "/clients/reports";

    public function __construct($config = null)
    {
        $this->url = $this->_url;
        
        parent::__construct($config);
    }

    /**
     * get data from defined Id
     *
     * @throws NullPointerException
     * @return array|mixed array data
     */
    public function get()
    {
        // build url
        $url = $this->base_url . $this->url;
        // get data from server
        $data = $this->getDataFromUrl($url);
        // parse result set and get data as array
        $result = $this->parseJsonData($data);
        
        return $result;
    }
}

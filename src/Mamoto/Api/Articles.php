<?php
namespace Mamoto\Api;

use Mamoto\Exceptions\NullPointerException;

class Articles extends AbstractApi
{

    private $_url = "/articles";

    private $_url_offers = "/articles/{id}/offers";

    private $_url_all_offers = "/articles/offers";

    const URL_SEARCH = "/articles/search";

    private $ean = null;

    public function __construct($config = null)
    {
        $this->url = $this->_url;
        
        parent::__construct($config);
    }

    /**
     * get setted EAN
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * set EAN Number
     *
     * @param String $ean            
     */
    public function setEan($ean)
    {
        $this->ean = $ean;
    }

    /**
     * get article with defined Id with all stored offer data
     *
     * @throws NullPointerException
     * @return mixed|array article with offer data
     */
    public function getWithOffers()
    {
        if ((int) $this->id <= 0) {
            throw new NullPointerException("Id is not set.");
        }
        
        // build url
        $url = $this->base_url . str_replace("{id}", $this->id, $this->_url_offers);
        // get data from server
        $data = $this->getDataFromUrl($url);
        // parse result set and get data as array
        $result = $this->parseJsonData($data);
        
        return $result;
    }

    /**
     * get all articles with all stored offer data
     *
     * @return mixed|array articles with offer data
     */
    public function getAllWithOffers()
    {
        // build url
        $url = $this->base_url . $this->_url_all_offers;
        // get data from server
        $data = $this->getDataFromUrl($url);
        // parse result set and get data as array
        $result = $this->parseJsonData($data);
        
        return $result;
    }

    /**
     * search article by EAN and return resultset
     *
     * @return mixed|array article data
     */
    public function search()
    {
        if (strlen($this->ean) <= 0) {
            throw new NullPointerException("EAN is not set.");
        }
        
        $params = array(
            "ean" => $this->ean
        );
        
        $url = $this->base_url . self::URL_SEARCH . "?" . http_build_query($params);
        
        // get data from server
        $data = $this->getDataFromUrl($url);
        // parse result set and get data as array
        $result = $this->parseJsonData($data);
        
        return $result;
    }
}

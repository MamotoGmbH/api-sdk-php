<?php
namespace Mamoto\Api;

use Mamoto\Exceptions\AuthException;
use Mamoto\Exceptions\NullPointerException;
use Mamoto\Exceptions\JsonException;
use Mamoto\Exceptions\ServerException;

abstract class AbstractApi
{
    // set SDK Version
    const VERSION = "0.1";

    protected $config = null;

    protected $base_url = null;

    protected $rawAnswer = false;

    protected $id = null;

    protected $login = null;

    protected $password = null;

    protected $url = null;

    protected $authObject = null;

    /**
     * constructor
     */
    public function __construct($config = null)
    {
        // if config is not set, try to read config file
        if ($config == null) {
            $config = parse_ini_file(__DIR__ . "/../../../config/config.ini");
        }
        
        $this->config = $config;
        
        if (isset($config["base_url"])) {
            $this->setBaseUrl($config["base_url"]);
        }
        if (isset($config["login"])) {
            $this->setLogin($config["login"]);
        }
        if (isset($config["password"])) {
            $this->setPassword($config["password"]);
        }
    }

    /**
     * set login credential
     *
     * @param string $login            
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * set password credential
     *
     * @param string $password            
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * set base url for API
     *
     * @param string $base_url            
     */
    public function setBaseUrl($base_url)
    {
        $this->base_url = $base_url;
    }

    /**
     * set unique id
     *
     * @param mixed $id            
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * return unique id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set TRUE if Raw answer (JSON) is requested
     *
     * @param boolean $raw
     *            TRUE if pure JSON requested, otherwise FALSE
     */
    public function setRawAnswer($raw)
    {
        $this->rawAnswer = boolval($raw);
    }

    /**
     * get answer type
     *
     * @return boolean TRUE if pure JSON requested, otherwise FALSE
     */
    public function getRawAnswer()
    {
        return boolval($this->rawAnswer);
    }

    /**
     * set authentication object
     *
     * @param Authentication $auth            
     */
    public function setAuthenticationObject(Authentication $auth)
    {
        $this->authObject = $auth;
    }

    /**
     * authentication client with defined login and password; return Auth Object
     *
     * @throws NullPointerException
     * @throws AuthException
     * @return \Mamoto\Api\Authentication
     */
    protected function getAuthenticationObject()
    {
        if ($this->authObject == null) {
            if (strlen($this->login) <= 0 || strlen($this->password) <= 0) {
                throw new NullPointerException("Login or Password is not set.");
            }
            $auth = new Authentication($this->config);
            if (! $auth->authenticate()) {
                throw new AuthException("Authentication Problem. Please check Login and Password.");
            }
            
            $this->authObject = $auth;
        }
        
        return $this->authObject;
    }

    /**
     * get data from url
     *
     * @param string $url            
     * @return mixed raw url data
     */
    protected function getDataFromUrl($url)
    {
        $auth = $this->getAuthenticationObject();
        
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HEADER, 0);
        // setting the authentication header
        curl_setopt($process, CURLOPT_HTTPHEADER, array(
            "Authorization: " . $auth->getTokenType() . " " . $auth->getAccessToken()
        ));
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $returnData = curl_exec($process);
        curl_close($process);
        
        return $returnData;
    }

    /**
     * parse Json data and return data as array
     *
     * @param unknown $data            
     * @throws JsonException
     * @throws ServerException
     * @return mixed|array resultset as array
     */
    protected function parseJsonData($data)
    {
        if ($data != FALSE) {
            // check, if raw answer is requestedd
            if ($this->getRawAnswer()) {
                return $data;
            }
            
            $json_data = json_decode($data, TRUE);
            if (isset($json_data["errors"])) {
                $err_msg = array();
                foreach ($json_data["errors"] as $error) {
                    $err_msg[] = $error["Message"];
                }
                throw new JsonException(implode(";", $err_msg));
            } else {
                return $json_data;
            }
        } else {
            throw new ServerException("Get No Data from Server.");
        }
    }

    /**
     * get data from defined Id
     *
     * @throws NullPointerException
     * @return array|mixed array data
     */
    public function get()
    {
        if ((int) $this->id <= 0) {
            throw new NullPointerException("Id is not set.");
        }
        
        // build url
        $url = $this->base_url . $this->url . "/" . $this->id;
        // get data from server
        $data = $this->getDataFromUrl($url);
        
        // parse result set and get data as array
        $result = $this->parseJsonData($data);
        
        return $result;
    }

    /**
     * get all data from url
     *
     * @return array|mixed get all data from url
     */
    public function getAll()
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

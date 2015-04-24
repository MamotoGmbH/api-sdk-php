<?php
namespace Mamoto\Api;

use Mamoto\Exceptions\JsonException;
use Mamoto\Exceptions\ServerException;

/**
 * Authentication Class to authenticate login/password credentials
 *
 * @author andre
 *        
 */
class Authentication extends AbstractApi
{

    const URL = "/oauth2/authorize";
    
    // access token data
    protected $access_token = null;
    
    // in how many seconds access token will expire
    protected $access_token_expires_in = null;
    
    // at what unix-timestamp it will be expire
    protected $access_token_expires_at = null;
    
    // returned token type
    protected $token_type = null;

    /**
     * return access_token
     *
     * @return string|null access token
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * return access token type
     *
     * @return string|null type
     */
    public function getTokenType()
    {
        return $this->token_type;
    }

    /**
     * authentication method
     *
     * @return boolean TRUE if authentication was sucessful
     * @throws \Exception
     */
    public function authenticate()
    {
        $returnData = FALSE;
        $process = curl_init($this->base_url . self::URL);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERPWD, $this->login . ":" . $this->password);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($process);
        
        if ($return != FALSE) {
            $json_data = json_decode($return, TRUE);
            if (isset($json_data["errors"])) {
                $err_msg = array();
                foreach ($json_data["errors"] as $error) {
                    $err_msg[] = $error["Message"];
                }
                throw new JsonException(implode(";", $err_msg));
            } else {
                $this->access_token = $json_data["access_token"];
                $this->token_type = $json_data["token_type"];
                $this->access_token_expires_in = (int) $json_data["expires_in"];
                $this->access_token_expires_at = time() + $this->access_token_expires_in - 1;
                
                $returnData = TRUE;
            }
        } else {
            throw new ServerException(curl_error($process));
        }
        curl_close($process);
        
        return $returnData;
    }
}

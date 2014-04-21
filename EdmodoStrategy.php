<?php
/**
 * Edmodo strategy for Opauth
 * based on https://developers.edmodo.com/edmodo-connect/docs/
 *
 * @copyright    Copyright 2014 Tackk, Inc. (https://tackk.com)
 * @package      Opauth.EdmodoStrategy
 * @license      MIT License
 */

class EdmodoStrategy extends OpauthStrategy
{
    /**
     * Compulsory config keys, listed as unassociative arrays
     * eg. array('app_id', 'app_secret');
     */
    public $expects = array('client_id', 'client_secret');

    /**
     * Optional config keys with respective default values, listed as associative arrays
     * eg. array('scope' => 'email');
     */
    public $defaults = array(
        'redirect_uri' => '{complete_url_to_strategy}int_callback',
        'scope' => 'basic',
    );

    /**
     * @var string The API Base Url
     */
    protected $apiUrl = 'https://api.edmodo.com/';


    /**
     * Make the Auth Request
     */
    public function request()
    {
        $this->clientGet("{$this->apiUrl}oauth/authorize", array(
            'client_id' => $this->strategy['client_id'],
            'redirect_uri' => $this->strategy['redirect_uri'],
            'scope' => $this->strategy['scope'],
            'response_type' => 'code'
        ));
    }

    /**
     * Internal callback, after Edmodo's OAuth
     */
    public function int_callback()
    {
        if (array_key_exists('code', $_GET) && ! empty($_GET['code'])) {
            list($credentials, $responseHeaders) = $this->getAccessToken($_GET['code']);

            if (! empty($credentials) && ! empty($credentials->access_token)) {
                $userInfo = $this->getUserInfo($credentials->access_token);

                if (! is_null($userInfo)) {
                    $this->prepareAuthInfo($credentials, $userInfo);
                }

                $this->callback();
            } else {
                $this->errorCallback(array(
                    'provider' => 'Edmodo',
                    'code' => 'access_token_error',
                    'message' => 'Failed when attempting to obtain access token',
                    'raw' => array(
                        'response' => $credentials,
                        'headers' => $responseHeaders
                    )
                ));
            }
        } else {
            $this->errorCallback(array(
                'provider' => 'Edmodo',
                'code' => $_GET['error'],
                'reason' => isset($_GET['error_reason']) ? $_GET['error_reason'] : '',
                'message' => $_GET['error_description'],
                'raw' => $_GET
            ));
        }
    }

    /**
     * Queries Edmodo API for user info
     *
     * @param   $accessToken
     * @return  array
     */
    private function getUserInfo($accessToken)
    {
        $userInfo = $this->serverGet("{$this->apiUrl}/users/me", array('access_token' => $accessToken), null, $headers);

        if (! empty($userInfo)) {
            return json_decode($userInfo);
        }

        $error = array(
            'provider' => 'Edmodo',
            'code' => 'userinfo_error',
            'message' => 'Failed when attempting to query for user information',
            'raw' => array(
                'response' => $userInfo,
                'headers' => $headers
            )
        );

        $this->errorCallback($error);
    }

    /**
     * Prepares the auth user info.
     *
     * @param $credentials
     * @param $userInfo
     */
    private function prepareAuthInfo($credentials, $userInfo)
    {
        $firstName = $this->getFromObject($userInfo, 'first_name');
        $lastName = $this->getFromObject($userInfo, 'last_name');
        if (is_null($firstName)) {
            $fullName = '';
        } else {
            $fullName = $firstName.' '.$lastName;
        }

        $avatars = $this->getFromObject($userInfo, 'avatars');

        $this->auth = array(
            'provider' => 'Edmodo',
            'uid' => $userInfo->id,
            'info' => array(
                'title' => $this->getFromObject($userInfo, 'user_title'),
                'name' => $fullName,
                'nickname' => $this->getFromObject($userInfo, 'username'),
                'image' => isset($avatars->large) ? $avatars->large : null,
            ),
            'credentials' => array(
                'token' => $credentials->access_token,
                'expires' => date('c', time() + $credentials->expires_in)
            ),
            'raw' => $userInfo
        );
    }

    /**
     * Gets the access token from Edmodo for the given oAuth code.
     *
     * @param $code
     * @return array
     */
    private function getAccessToken($code)
    {
        $url = "{$this->apiUrl}oauth/token";

        $params = array(
            'client_id' =>$this->strategy['client_id'],
            'client_secret' => $this->strategy['client_secret'],
            'redirect_uri'=> $this->strategy['redirect_uri'],
            'grant_type' => 'authorization_code',
            'code' => trim($code)
        );
        $response = $this->serverPost($url, $params, null, $responseHeaders);

        return array(json_decode($response), $responseHeaders);
    }

    /**
     * Gets a parameter from an object, returning null if it is not defined.
     *
     * @param  $obj
     * @param  $param
     * @return mixed
     */
    private function getFromObject($obj, $param)
    {
        return isset($obj->$param) ? $obj->$param : null;
    }
}

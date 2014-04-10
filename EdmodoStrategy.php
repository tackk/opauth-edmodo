<?php
/**
 * Edmodo strategy for Opauth
 * based on https://developers.edmodo.com/edmodo-connect/docs/
 *
 * More information on Opauth: http://opauth.org
 *
 * @copyright    Copyright 2014 Tackk, Inc. (https://tackk.com)
 * @link         http://opauth.org
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
        'redirect_uri' => '{complete_url_to_strategy}int_callback'
    );

    protected $apiUrl = 'https://api.edmodo.com/';


    /**
     * Auth request
     */
    public function request()
    {
        // pass
    }
    
    /**
     * Internal callback, after Instagram's OAuth
     */
    public function int_callback()
    {
        // pass
    }
    
    /**
     * Queries Edmodo API for user info
     *
     * @param   integer $uid
     * @param   string  $access_token
     * @return  array   Parsed JSON results
     */
    private function userinfo($uid, $access_token)
    {
        // pass
    }
}

<?php
/**
 * This file is part of the ZfcUserAdLdap Module (https://github.com/RobQuistNL/ZfcUserAdLdap)
 *
 * Copyright (c) 2013 Rob Quist (https://github.com/RobQuistNL)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.txt that was distributed with this source code.
 */
namespace ZfcUserAdLdap\Service;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream as LogWriter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\Ldap as AuthAdapter;
use Zend\Ldap\Exception\LdapException;

class LdapInterface {

    private $config;
    protected $adldap;
    protected $entity;
    protected $active_server;
    protected $error;

    public function __construct($config) {
	    $this->config = $config;
        try {
            $this->bind();
        } catch (\Exception $exc) {
            return $this->error;
        }

    }

    /**
     *
     * @param type $msg
     * @param type $log_level EMERG=0, ALERT=1, CRIT=2, ERR=3, WARN=4, NOTICE=5, INFO=6, DEBUG=7
     */
    public function log($msg, $priority = 5) {
        echo '<br/>Logger called';
        return 1;
    }

    /**
     * bind
     * Bind $this->adldap to a valid LDAP handle
     */
    public function bind() {
        include (dirname(__FILE__) . "/../../../vendor/adLDAP/src/adLDAP.php");
        try {
            $this->adldap = new \adLDAP($this->config);
        }
        catch (\adLDAPException $e) {
            echo $e; 
            die;
        }
    }

    /**
     * 
     * @param string $username
     * @param string $password
     * @return User information if success, false if not. array|boolean
     */
    function authenticate($username, $password) {
        $auth = $this->adldap->authenticate($username, $password);
        
        if ($auth){
            return $this->adldap->user()->info($username);
        } 
        return false;
    }

}

<?php
/**
 * This file is part of the ZfcUserAdLdap Module (https://github.com/RobQuistNL/ZfcUserAdLdap)
 *
 * Copyright (c) 2013 Rob Quist (https://github.com/RobQuistNL)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.txt that was distributed with this source code.
 */
namespace ZfcUserAdLdap\Authentication\Adapter;

use Zend\Authentication\Storage;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcUserAdLdap\Mapper\User as UserMapperInterface;
use ZfcUser\Options\AuthenticationOptionsInterface;
use ZfcUser\Authentication\Adapter\ChainableAdapter as AdapterChain;
use ZfcUser\Authentication\Adapter\AdapterChainEvent as AuthEvent;

class Ldap implements AdapterChain, ServiceManagerAwareInterface {

    /**
     * @var UserMapperInterface
     */
    protected $mapper;

    /**
     * @var closure / invokable object
     */
    protected $credentialPreprocessor;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var AuthenticationOptionsInterface
     */
    protected $options;

    /**
     * @var Storage\StorageInterface
     */
    protected $storage;
    
    public function authenticate(AuthEvent $e) {
        
        $mapper = new \ZfcUserAdLdap\Mapper\User(
                $this->getServiceManager()->get('ldap_interface'), $this->getServiceManager()->get('zfcuser_module_options')
        );
        
        $this->setMapper($mapper);

        $identity = $e->getRequest()->getPost()->get('identity');
        $credential = $e->getRequest()->getPost()->get('credential');
        
        $userObject = NULL;
        /*
         * In some special case scenarios some LDAP providers allow LDAP
         * logins via email address both as uid or as mail address lookup,
         * so to provide an interface to both we do a validator instead of
         * a loop to verify if it's an email address or not and pull the user.
         *
         * Authentication will then be done on the *actual* username set in LDAP
         * which in some cases may be case sensitive which could cause an issue
         * where users do not exist if their email was created with upper case
         * letters and the user types in lower case.
         *
         * $fields = $this->getOptions()->getAuthIdentityFields();
         */
        
        $userObject = $this->getMapper()->authenticate($identity, $credential);
        
        if ($userObject === FALSE) {
            // Password does not match
            $e->setCode(AuthenticationResult::FAILURE_CREDENTIAL_INVALID)
                    ->setMessages(array($userObject));
            $this->setSatisfied(false);
            return false;
        }
        
        $e->setIdentity($userObject->getEntity());

        $this->setSatisfied(true);
        $storage = $this->getStorage()->read();
        $storage['identity'] = $e->getIdentity();
        $this->getStorage()->write($storage);
        $e->setCode(AuthenticationResult::SUCCESS)->setMessages(array('Authentication successful.'));

    }

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter has been set.
     *
     * @return Storage\StorageInterface
     */
    public function getStorage() {
        if (null === $this->storage) {
            $this->setStorage(new Storage\Session(get_called_class()));
        }

        return $this->storage;
    }

    /**
     * Sets the persistent storage handler
     *
     * @param  Storage\StorageInterface $storage
     * @return AbstractAdapter Provides a fluent interface
     */
    public function setStorage(Storage\StorageInterface $storage) {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Check if this adapter is satisfied or not
     *
     * @return bool
     */
    public function isSatisfied() {
        $storage = $this->getStorage()->read();
        return (isset($storage['is_satisfied']) && true === $storage['is_satisfied']);
    }

    /**
     * Set if this adapter is satisfied or not
     *
     * @param bool $bool
     * @return AbstractAdapter
     */
    public function setSatisfied($bool = true) {
        $storage = $this->getStorage()->read() ? : array();
        $storage['is_satisfied'] = $bool;
        $this->getStorage()->write($storage);
        return $this;
    }

    public function preprocessCredential($credential) {
        $processor = $this->getCredentialPreprocessor();
        if (is_callable($processor)) {
            return $processor($credential);
        }
        return $credential;
    }

    /**
     * getMapper
     *
     * @return UserMapperInterface
     */
    public function getMapper() {
        if (null === $this->mapper) {
            $this->mapper = $this->getServiceManager()->get('zfcuser_user_mapper');
        }
        return $this->mapper;
    }

    /**
     * setMapper
     *
     * @param UserMapperInterface $mapper
     * @return Db
     */
    public function setMapper(UserMapperInterface $mapper) {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * Get credentialPreprocessor.
     *
     * @return \callable
     */
    public function getCredentialPreprocessor() {
        return $this->credentialPreprocessor;
    }

    /**
     * Set credentialPreprocessor.
     *
     * @param $credentialPreprocessor the value to be set
     */
    public function setCredentialPreprocessor($credentialPreprocessor) {
        $this->credentialPreprocessor = $credentialPreprocessor;
        return $this;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager() {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $locator
     * @return void
     */
    public function setServiceManager(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param AuthenticationOptionsInterface $options
     */
    public function setOptions(AuthenticationOptionsInterface $options) {
        $this->options = $options;
    }

    /**
     * @return AuthenticationOptionsInterface
     */
    public function getOptions() {
        if (!$this->options instanceof AuthenticationOptionsInterface) {
            $this->setOptions($this->getServiceManager()->get('zfcuser_module_options'));
        }
        return $this->options;
    }

}
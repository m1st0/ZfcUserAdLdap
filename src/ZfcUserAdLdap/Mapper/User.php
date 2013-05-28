<?php
/**
 * This file is part of the ZfcUserAdLdap Module (https://github.com/RobQuistNL/ZfcUserAdLdap)
 *
 * Copyright (c) 2013 Rob Quist (https://github.com/RobQuistNL)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.txt that was distributed with this source code.
 */
namespace ZfcUserAdLdap\Mapper;


use ZfcUser\Mapper\User as ZfcUserMapper;
use ZfcUserAdLdap\Options\ModuleOptions;
use ZfcUserAdLdap\Service\LdapInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;


class User extends ZfcUserMapper
{
    /** @var \ZfcUserAdLdap\Service\LdapInterface */
    protected $ldap;
    /**
     * @var \ZfcUserAdLdap\Options\ModuleOptions
     */
    protected $options;

    public function __construct(LdapInterface $ldap, ModuleOptions $options)
    {
        $this->ldap      = $ldap;
        $this->options = $options;
        $entityClass = $this->options->getUserEntityClass();
        $this->entity = new $entityClass();
    }

    public function getEntity()
    {
        return $this->entity;
    }
    
    public function authenticate($identity,$credential){
        $auth = $this->ldap->authenticate($identity, $credential);
        if ($auth !== FALSE) {
            $this->entity->setDisplayName($auth[0]['displayname'][0]);
            $this->entity->setEmail('');
            $this->entity->setId($auth[0]['objectsid'][0]);
            $this->entity->setUsername($auth[0]['samaccountname'][0]);
            return $this; 
       } else {
           return false;
       }
    }

    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        return FALSE;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        return FALSE;
    }
}
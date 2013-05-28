<?php

/**
 * This file is part of the ZfcUserAdLdap Module (https://github.com/RobQuistNL/ZfcUserAdLdap)
 *
 * Copyright (c) 2013 Rob Quist (https://github.com/RobQuistNL/ZfcUserAdLdap)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.txt that was distributed with this source code.
 */

namespace ZfcUserAdLdap;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module {

    public function onBootstrap(MvcEvent $e) {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig() {
        return array(
            'invokables' => array(
                'ZfcUserAdLdap\Authentication\Adapter\Ldap' => 'ZfcUserAdLdap\Authentication\Adapter\Ldap',
            ),
            'factories' => array(
                'zfcuser_ldap_service' => 'ZfcUserAdLdap\ServiceFactory\Ldap',
                'ldap_interface' => 'ZfcUserAdLdap\ServiceFactory\LdapServiceFactory',
                'zfcuser_module_options' => function ($sm) {
                    $config = $sm->get('Configuration');
                    return new Options\ModuleOptions(isset($config['zfcuser']) ? $config['zfcuser'] : array());
                },
                'zfcuser_user_mapper' => function ($sm) {
                    return new \ZfcUserAdLdap\Mapper\User(
                            $sm->get('ldap_interface'), $sm->get('zfcuser_module_options')
                    );
                },
                'zfcuser_user_db_mapper' => function ($sm) {
                    $options = $sm->get('zfcuser_module_options');
                    $mapper = new \ZfcUser\Mapper\User();
                    $mapper->setDbAdapter($sm->get('zfcuser_zend_db_adapter'));
                    $entityClass = $options->getUserEntityClass();
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new \ZfcUser\Mapper\UserHydrator());
                    return $mapper;
                },
            ),
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

}

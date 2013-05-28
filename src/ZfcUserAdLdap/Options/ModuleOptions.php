<?php
/**
 * This file is part of the ZfcUserAdLdap Module (https://github.com/RobQuistNL/ZfcUserAdLdap)
 *
 * Copyright (c) 2013 Rob Quist (https://github.com/RobQuistNL)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.txt that was distributed with this source code.
 */
namespace ZfcUserAdLdap\Options;

use ZfcUser\Options\ModuleOptions as BaseModuleOptions;

class ModuleOptions extends BaseModuleOptions {

    /**
     * @var string
     */
    protected $userEntityClass = 'ZfcUserAdLdap\Entity\User';

    /**
     * @var bool
     */
    protected $enableDefaultEntities = true;

    /**
     * @param boolean $enableDefaultEntities
     */
    public function setEnableDefaultEntities($enableDefaultEntities) {
        $this->enableDefaultEntities = $enableDefaultEntities;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableDefaultEntities() {
        return $this->enableDefaultEntities;
    }

}
<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Soap\Componants;

use ArrayObject;
use Splash\Components\Webservice as BaseWebservice;
use Splash\Core\SplashCore as Splash;

/**
 * @abstract    Spash Soap Connector Webservice Manager
 */
final class Webservice extends BaseWebservice
{
    //====================================================================//
    //  WEBSERVICE PARAMETERS MANAGEMENT
    //====================================================================//

    /**
     * @abstract   Override Setup Function
     *
     * @return false
     */
    public function setup()
    {
        return false;
    }

    /**
     * @abstract   Initialise Webservice Parameters
     *
     * @param array $config Connector Configuration
     *
     * @return $this
     */
    public function configure(array $config)
    {
        //====================================================================//
        // Read Parameters
        $this->id = $config['WsIdentifier'];
        $this->key = $config['WsEncryptionKey'];
        //====================================================================//
        // Detect Server Host Address
        $this->host = self::getNormalizedHostUrl(
            $config['WsHost'],
            $config['WsPath'],
            isset($config['EnableHttps']) ? $config['EnableHttps'] : true
        );
        //====================================================================//
        // Detect Http Auth Configuration
        if (isset($config['HttpAuth']) && !empty($config['HttpAuth'])) {
            $this->httpAuth = true;
            $this->httpUser = $config['HttpUser'];
            $this->httpPassword = $config['HttpPassword'];
        }

        //====================================================================//
        //  Load Translation File
        Splash::translator()->load("ws");

        return $this;
    }

    /**
     * @abstract   Extract Data from Task response
     *
     * @param ArrayObject $task WebService Response Task
     *
     * @return array|false
     */
    public static function extractData(ArrayObject $task)
    {
        if (!isset($task["data"])) {
            return false;
        }
        if (is_a($task["data"], ArrayObject::class)) {
            return $task["data"]->getArrayCopy();
        }

        return $task["data"];
    }

    /**
     * @abstract   Prepare & Normalize Host Url From Current Server
     *
     * @param string $wsHost  Server Host Url
     * @param string $wsPath  Server Path
     * @param bool   $enHttps Force Https Url if no Schema Defined
     *
     * @return string
     */
    private static function getNormalizedHostUrl(string $wsHost, string $wsPath, bool $enHttps = null)
    {
        $schema = null;
        //====================================================================//
        // If Given Url Doesn't include Prefix
        if ((false === strpos($wsHost, "http://")) && (false === strpos($wsHost, "https://"))) {
            //====================================================================//
            // If Https Mode is Enabled
            $schema = (false !== $enHttps) ? 'https://' : 'http://';
        }
        //====================================================================//
        // If Given Url include Prefix
        return  $schema.$wsHost.$wsPath;
    }
}

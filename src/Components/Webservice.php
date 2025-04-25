<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Soap\Components;

use ArrayObject;
use Splash\Core\Client\Splash;
use Splash\Core\Components\Webservice as BaseWebservice;

/**
 * Splash Soap Connector Webservice Manager
 */
final class Webservice extends BaseWebservice
{
    //====================================================================//
    //  WEBSERVICE PARAMETERS MANAGEMENT
    //====================================================================//

    /**
     * Override Setup Function
     *
     * @return bool
     */
    public function setup(): bool
    {
        return false;
    }

    /**
     * Initialise Webservice Parameters
     *
     * @param array $config Connector Configuration
     *
     * @return $this
     */
    public function configure(array $config): self
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
     * Extract Array Data from Task response
     *
     * @param null|array $task WebService Response Task
     *
     * @return null|array
     */
    public static function extractArray(?array $task): ?array
    {
        if (is_null($task) || !isset($task["data"])) {
            return null;
        }
        foreach ($task["data"] as &$item) {
            $item = ($item instanceof ArrayObject) ? $item->getArrayCopy() : $item;
        }

        return $task["data"];
    }

    /**
     * Extract String Data from Task response
     *
     * @param null|array $task WebService Response Task
     *
     * @return null|string
     */
    public static function extractString(?array $task): ?string
    {
        if (is_null($task) || !isset($task["data"]) || !is_scalar($task["data"])) {
            return null;
        }

        return (string) $task["data"];
    }

    /**
     * Prepare & Normalize Host Url From Current Server
     *
     * @param string $wsHost  Server Host Url
     * @param string $wsPath  Server Path
     * @param bool   $enHttps Force Https Url if no Schema Defined
     *
     * @return string
     */
    private static function getNormalizedHostUrl(string $wsHost, string $wsPath, bool $enHttps = null): string
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

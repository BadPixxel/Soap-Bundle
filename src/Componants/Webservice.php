<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2018 Splash Sync  <www.splashsync.com>
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
     * @return  false
     */
    public function setup()
    {
        return false;
    }
    
    /**
     * @abstract   Initialise Webservice Parameters
     * @param   array   $Config         Connector Configuration
     * @return  $this
     */
    public function configure(array $Config)
    {
        //====================================================================//
        // Read Parameters
        $this->id       =   $Config['WsIdentifier'];
        $this->key      =   $Config['WsEncryptionKey'];
        //====================================================================//
        // Detect Server Host Address
        $this->host     =   self::getNormalizedHostUrl(
                $Config['WsHost'],
                $Config['WsPath'],
                isset($Config['EnableHttps']) ? $Config['EnableHttps'] : true
            );
        //====================================================================//
        //  Load Translation File
        Splash::translator()->load("ws");

        return $this;
    }

    /**
     * @abstract   Extract Data from Task response
     *
     * @param      ArrayObject    $Task         WebService Response Task
     *
     * @return     array|false
     */
    public static function extractData(ArrayObject $Task)
    {
        if (!isset($Task["data"])) {
            return false;
        }
        if (is_a($Task["data"], ArrayObject::class)) {
            return $Task["data"]->getArrayCopy();
        }

        return $Task["data"];
    }
    
    /**
     * @abstract   Prepare & Normalize Host Url From Current Server
     *
     * @param   string  $WsHost         Server Host Url
     * @param   string  $WsPath         Server Path
     * @param   bool    $EnHttps        Force Https Url if no Schema Defined
     *
     * @return  string
     */
    private static function getNormalizedHostUrl(string $WsHost, string $WsPath, bool $EnHttps = true)
    {
        $Schema =   null;
        //====================================================================//
        // If Given Url Doesn't include Prefix
        if ((false === strpos($WsHost, "http://")) && (false === strpos($WsHost, "https://"))) {
            //====================================================================//
            // If Https Mode is Enabled
            $Schema = $EnHttps ? 'https://' : 'http://';
        }
        //====================================================================//
        // If Given Url include Prefix
        return  $Schema . $WsHost . $WsPath;
    }
}

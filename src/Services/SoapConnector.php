<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Soap\Services;

use ArrayObject;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Connectors\Soap\Componants\Webservice;
use Splash\Connectors\Soap\Form\CompleteSoapType;
use Splash\Connectors\Soap\Form\SimpleSoapType;
use Splash\Core\SplashCore as Splash;
use Splash\Models\Helpers\TestHelper;

/**
 * Splash Soap Connector
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
final class SoapConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    public function ping() : bool
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Create Webservice Component
        $webservice = (new Webservice())->configure($this->getConfiguration());
        //====================================================================//
        // Perform Ping Test
        $response = $webservice->call(SPL_S_PING, null, true);
        if ($response && isset($response["result"]) && !empty($response["result"])) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function connect() : bool
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Create Webservice Component
        $webservice = (new Webservice())->configure($this->getConfiguration());
        //====================================================================//
        // Perform Connect Test
        $response = $webservice->call(SPL_S_CONNECT);
        if ($response && isset($response["result"]) && !empty($response["result"])) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function informations(ArrayObject  $informations) : ArrayObject
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return $informations;
        }
        //====================================================================//
        // Execute Generic WebService Action
        $response = $this->doGeneric(
            $this->getConfiguration(),                      // WebService Configuration
            SPL_S_ADMIN,                                  // Request Service
            SPL_F_GET_INFOS,                                // Requested Function
            "Read Server Infos"                             // Action Description Translator Tag
        );
        //====================================================================//
        // Check Response
        if (!$response) {
            return $informations;
        }
        //====================================================================//
        // Import Response Object
        foreach ($response as $key => $value) {
            $informations->offsetSet($key, $value);
        }

        return $informations;
    }

    /**
     * {@inheritdoc}
     */
    public function selfTest() : bool
    {
        $config = $this->getConfiguration();
        Splash::translator()->load("ws");

        //====================================================================//
        // Verify Minimum WebService Configuration is Set
        //====================================================================//
        if (true !== $this->verifyCoreConfiguration($config)) {
            return false;
        }

        //====================================================================//
        // Verify Http Auth Configuration
        //====================================================================//
        if (true !== $this->verifyHttpAuthConfiguration($config)) {
            return false;
        }

        return true;
    }

    //====================================================================//
    // Objects Interfaces
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function getAvailableObjects() : array
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return array();
        }
        //====================================================================//
        // Execute Generic WebService Action
        $response = $this->doGeneric(
            $this->getConfiguration(),                      // WebService Configuration
            SPL_S_OBJECTS,                                  // Request Service
            SPL_F_OBJECTS,                                  // Requested Function
            "Read Objects List"                             // Action Description
        );

        return (false === $response) ? array() : $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectDescription(string $objectType) : array
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return array();
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $parameters = array(
            "type" => $objectType,
        );
        //====================================================================//
        // Execute Generic WebService Action
        $response = $this->doGeneric(
            $this->getConfiguration(),              // WebService Configuration
            SPL_S_OBJECTS,                   // Request Service
            SPL_F_DESC,                       // Requested Function
            "Read Object Description",    // Action Description
            $parameters                             // Requests Parameters Array
        );

        return (false === $response) ? array() : $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectFields(string $objectType) : array
    {
        //====================================================================//
        // Safety Check => Verify Selftest Pass
        if (!$this->selfTest()) {
            return array();
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $parameters = array(
            "type" => $objectType,
        );
        //====================================================================//
        // Execute Generic WebService Action
        $response = $this->doGeneric(
            $this->getConfiguration(),          // WebService Configuration
            SPL_S_OBJECTS,               // Request Service
            SPL_F_FIELDS,                 // Requested Function
            "Read Object Fields",     // Action Description Translator Tag
            $parameters                         // Request Parameters Array
        );

        return (false === $response) ? array() : $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectList(string $objectType, string $filter = null, array $parameters = array()) : array
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return array();
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $params = array(
            "type" => $objectType,
            "filters" => $filter,
            "params" => $parameters,
        );
        //====================================================================//
        // Execute Generic WebService Action
        $response = $this->doGeneric(
            $this->getConfiguration(),         // WebService Configuration
            SPL_S_OBJECTS,              // Request Service
            SPL_F_LIST,                 // Requested Function
            "Read Objects List",     // Action Description Translator Tag
            $params                            // Request Parameters Array
        );

        return (false === $response) ? array() : $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getObject(string $objectType, $objectIds, array $fieldsList)
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Safety Checks
        if (empty($objectType)) {
            return false;
        }
        //====================================================================//
        // Take Care of Single Read Requests
        if (is_scalar($objectIds)) {
            return $this->getOneObject($objectType, (string) $objectIds, $fieldsList);
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $parameters = array();
        foreach ($objectIds as $objectId) {
            $parameters[] = array(
                "type" => $objectType,
                "id" => $objectId,
                "fields" => $fieldsList,
            );
        }
        //====================================================================//
        // Execute Combo WebService Action
        return $this->doCombo(
            $this->getConfiguration(),              // WebService Configuration
            SPL_S_OBJECTS,                   // Request Service
            SPL_F_GET,                        // Requested Function
            "Read Object Data",           // Action Description Translator Tag
            $parameters                             // Request Parameters Array
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setObject(string $objectType, string $objectId = null, array $objectData = array())
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $parameters = array(
            "type" => $objectType,
            "id" => $objectId,
            "fields" => $objectData,
        );
        //====================================================================//
        // Execute Generic WebService Action
        $response = $this->doGeneric(
            $this->getConfiguration(),          // WebService Configuration
            SPL_S_OBJECTS,               // Request Service
            SPL_F_SET,                    // Requested Function
            "Write Object Data",           // Action Description Translator Tag
            $parameters                         // Request Parameters Array
        );
        //====================================================================//
        // PhpUnit Helper => Submit Object Commit
        if ((false !== $response) && !empty($response)) {
            $action = empty($objectId) ? SPL_A_CREATE : SPL_A_UPDATE;
            TestHelper::simObjectCommit($objectType, $response, $action);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteObject(string $objectType, string $objectId) : bool
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $parameters = array(
            "type" => $objectType,
            "id" => $objectId,
        );
        //====================================================================//
        // Execute Generic WebService Action
        $response = $this->doGeneric(
            $this->getConfiguration(),          // WebService Configuration
            SPL_S_OBJECTS,               // Request Service
            SPL_F_DEL,                    // Requested Function
            "Delete Object",           // Action Description Translator Tag
            $parameters                          // Request Parameters Array
        );
        //====================================================================//
        // PhpUnit Helper => Submit Object Commit
        if ((true === $response) || ("1" === $response)) {
            TestHelper::simObjectCommit($objectType, $objectId, SPL_A_DELETE);
        }

        return $response;
    }

    //====================================================================//
    // Files Interfaces
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function getFile(string $filePath, string $fileMd5)
    {
        //====================================================================//
        // Safety Check => Verify Selftest Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Safety Checks
        if (empty($filePath) || empty($fileMd5)) {
            return array();
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $parameters = array(
            "path" => $filePath,
            "md5" => $fileMd5,
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->doGeneric(
            $this->getConfiguration(),          // WebService Configuration
            SPL_S_FILE,                  // Request Service
            SPL_F_GETFILE,                // Requested Function
            "Read File",              // Action Description Translator Tag
            $parameters                         // Request Parameters Array
        );
    }

    //====================================================================//
    // Widgets Interfaces
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function getAvailableWidgets() : array
    {
        //====================================================================//
        // Safety Check => Verify Selftest Pass
        if (!$this->selfTest()) {
            return array();
        }
        //====================================================================//
        // Execute Generic WebService Action
        $response = $this->doGeneric(
            $this->getConfiguration(),            // WebService Configuration
            SPL_S_WIDGETS,                 // Request Service
            SPL_F_WIDGET_LIST,              // Requested Function
            "Read Widgets List"         // Action Description Translator Tag
        );

        return (false === $response) ? array() : $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetDescription(string $widgetType) : array
    {
        //====================================================================//
        // Safety Check => Verify Selftest Pass
        if (!$this->selfTest()) {
            return array();
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $parameters = array(
            "type" => $widgetType,
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->doGeneric(
            $this->getConfiguration(),              // WebService Configuration
            SPL_S_WIDGETS,                   // Request Service
            SPL_F_WIDGET_DEFINITION,          // Requested Function
            "Read Widget Definition",     // Action Description Translator Tag
            $parameters                             // Request Parameters Array
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetContents(string $widgetType, array $params = array())
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Convert Dates to Splash String Format
        if (isset($params["DateStart"]) && is_a($params["DateStart"], "DateTime")) {
            $params["DateStart"] = $params["DateStart"]->format(SPL_T_DATETIMECAST);
        }
        if (isset($params["DateEnd"]) && is_a($params["DateEnd"], "DateTime")) {
            $params["DateEnd"] = $params["DateEnd"]->format(SPL_T_DATETIMECAST);
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $parameters = array(
            "type" => $widgetType,
            "params" => $params,
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->doGeneric(
            $this->getConfiguration(),          // WebService Configuration
            SPL_S_WIDGETS,               // Request Service
            SPL_F_WIDGET_GET,            // Requested Function
            "Read Widget Contents",   // Action Description Translator Tag
            $parameters                         // Request Parameters Array
        );
    }

    //====================================================================//
    // Profile Interfaces
    //====================================================================//

    /**
     * Get Connector Profile Informations
     *
     * @return array
     */
    public function getProfile() : array
    {
        return array(
            'enabled' => true,                              // is Connector Enabled
            'beta' => true,                                 // is this a Beta release
            'type' => self::TYPE_SERVER,                    // Connector Type or Mode
            'name' => 'soap',                               // Connector code (lowercase, no space allowed)
            'connector' => 'splash.connectors.soap',        // Connector Symfony service
            'title' => 'Soap Connector',                    // Public short name
            'label' => 'The Soap Connector',                // Public long name
            'domain' => false,                              // Translation domain for names
            'ico' => 'bundles/splash/splash-ico.png',       // Public Icon path
            'www' => 'www.splashsync.com',                  // Website Url
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectedTemplate() : string
    {
        return "@Soap/Profile/connected.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getOfflineTemplate() : string
    {
        return "@Soap/Profile/offline.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getNewTemplate() : string
    {
        return "@Soap/Profile/new.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getFormBuilderName() : string
    {
        return $this->getParameter("Extended", false) ? CompleteSoapType::class : SimpleSoapType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasterAction()
    {
        return "SoapBundle:Soap:master";
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicActions() : array
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getSecuredActions() : array
    {
        return array(
            "newhost" => "SoapBundle:Actions:host",
            "newkeys" => "SoapBundle:Actions:keys",
        );
    }

    //====================================================================//
    //  HIGH LEVEL WEBSERVICE CALLS
    //====================================================================//

    /**
     * Perform Generic Soap Action
     *
     * @param array  $config  WebService Configuration
     * @param string $service Service Method to reach
     * @param string $action  Service Action to perform
     * @param string $desc    Action Description for Information
     * @param array  $params  Action Parameters.
     *
     * @return mixed
     */
    private function doGeneric(array $config, string $service, string $action, string $desc, array $params = array())
    {
        //====================================================================//
        // Create Webservice Component
        $webservice = (new Webservice())->configure($config);
        //====================================================================//
        // Add Task To Queue
        $webservice->addTask($action, $params, $desc);
        //====================================================================//
        // Perform Request
        $response = $webservice->call($service);
        //====================================================================//
        // Verify Response is Ok
        if (!isset($response->result) || empty($response->result) || empty($response->tasks)) {
            return false;
        }
        //====================================================================//
        // Get Next Task Result
        $tasks = $response->tasks->getArrayCopy();
        $task = array_shift($tasks);
        //====================================================================//
        // Return Task Data
        return Webservice::extractData($task);
    }

    /**
     * Perform Multiple Soap Action
     *
     * @param array  $config      WebService Configuration
     * @param string $service     Service Method to reach
     * @param string $action      Service Action to perform
     * @param string $description Action Description for Information
     * @param array  $parameters  Array of Action Parameters.
     *
     * @return mixed
     */
    private function doCombo(
        array $config,
        string $service,
        string $action,
        string $description,
        array $parameters = array()
    ) {
        //====================================================================//
        // Create Webservice Component
        $webservice = (new Webservice())->configure($config);
        //====================================================================//
        // Add Task To Queue
        foreach ($parameters as $params) {
            $webservice->addTask($action, $params, $description);
        }
        //====================================================================//
        // Perform Request
        $response = $webservice->call($service);
        //====================================================================//
        // Verify Response is Ok
        if (!isset($response->result) || empty($response->result) || empty($response->tasks)) {
            return false;
        }
        //====================================================================//
        // Get Tasks Results
        $results = array();
        foreach ($response->tasks as $task) {
            $results[] = Webservice::extractData($task);
        }
        //====================================================================//
        // Return Tasks Results
        return   $results;
    }

    /**
     * Read One Object Action
     *
     * @param string $objectType Object Type Name
     * @param string $objectId   Object ID
     * @param array  $fieldsList List of Field IDs to Read
     *
     * @return mixed
     */
    private function getOneObject(string $objectType, string $objectId, array $fieldsList)
    {
        //====================================================================//
        // Initiate Tasks parameters array
        $parameters = array(
            "type" => $objectType,
            "id" => $objectId,
            "fields" => $fieldsList,
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->doGeneric(
            $this->getConfiguration(),          // WebService Configuration
            SPL_S_OBJECTS,               // Request Service
            SPL_F_GET,                    // Requested Function
            "Read Object Data",            // Action Description Translator Tag
            $parameters                         // Request Parameters Array
        );
    }

    //====================================================================//
    //  LOW LEVEL PRIVATE FUNCTIONS
    //====================================================================//

    /**
     * Verify Core Configuration
     *
     * @param array $config Connector Configuration
     *
     * @return bool
     */
    private function verifyCoreConfiguration(array $config) : bool
    {
        //====================================================================//
        // Verify Server Id not empty
        if (!isset($config['WsIdentifier']) || empty($config['WsIdentifier'])) {
            return Splash::log()->err('ErrWsNoId');
        }

        //====================================================================//
        // Verify Server Key not empty
        if (!isset($config['WsEncryptionKey']) || empty($config['WsEncryptionKey'])) {
            return Splash::log()->err('ErrWsNoKey');
        }

        //====================================================================//
        // Verify host address is present
        if (!isset($config['WsHost']) || empty($config['WsHost'])) {
            return Splash::log()->err('ErrWsNoHost');
        }
        if (!isset($config['WsPath'])) {
            return Splash::log()->err('ErrWsNoHost');
        }

        return true;
    }

    /**
     * Verify Http Auth Configuration
     *
     * @param array $config Connector Configuration
     *
     * @return bool
     */
    private function verifyHttpAuthConfiguration(array $config) : bool
    {
        //====================================================================//
        // Verify Http Auth Configuration
        if (isset($config['HttpAuth']) && !empty($config['HttpAuth'])) {
            if (!isset($config['HttpUser']) || empty($config['HttpUser'])) {
                return Splash::log()->err('ErrWsNoHttpUser');
            }
            if (!isset($config['HttpPassword']) || empty($config['HttpPassword'])) {
                return Splash::log()->err('ErrWsNoHttpPwd');
            }
        }

        return true;
    }
}

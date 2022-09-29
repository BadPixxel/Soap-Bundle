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

namespace Splash\Connectors\Soap\Services;

use ArrayObject;
use Splash\Bundle\Interfaces\Connectors\PrimaryKeysInterface;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Connectors\Soap\Components\Webservice;
use Splash\Connectors\Soap\Form\CompleteSoapType;
use Splash\Connectors\Soap\Form\SimpleSoapType;
use Splash\Core\SplashCore as Splash;
use Splash\Models\Helpers\TestHelper;

/**
 * Splash Soap Connector
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
final class SoapConnector extends AbstractConnector implements PrimaryKeysInterface
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
        $response = Webservice::extractArray($this->doGeneric(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Service
            SPL_S_ADMIN,
            // Requested Function
            SPL_F_GET_INFOS,
            // Action Description Translator Tag
            "Read Server Infos"
        ));
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
        $task = $this->doGeneric(
            $this->getConfiguration(),      // WebService Configuration
            SPL_S_OBJECTS,           // Request Service
            SPL_F_OBJECTS,            // Requested Function
            "Read Objects List"        // Action Description
        );

        return Webservice::extractArray($task)?: array();
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectDescription(string $objectType): array
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return array();
        }
        //====================================================================//
        // Execute Generic WebService Action
        $task = $this->doGeneric(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Service
            SPL_S_OBJECTS,
            // Requested Function
            SPL_F_DESC,
            // Action Description
            "Read Object Description",
            // Requests Parameters Array
            array(
                "type" => $objectType,
            )
        );

        return Webservice::extractArray($task)?: array();
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectFields(string $objectType) : array
    {
        //====================================================================//
        // Safety Check => Verify Self-test Pass
        if (!$this->selfTest()) {
            return array();
        }
        //====================================================================//
        // Execute Generic WebService Action
        $task = $this->doGeneric(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Service
            SPL_S_OBJECTS,
            // Requested Function
            SPL_F_FIELDS,
            // Action Description Translator Tag
            "Read Object Fields",
            // Request Parameters Array
            array(
                "type" => $objectType,
            )
        );

        return Webservice::extractArray($task)?: array();
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectList(string $objectType, string $filter = null, array $params = array()) : array
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return array();
        }
        //====================================================================//
        // Execute Generic WebService Action
        $task = $this->doGeneric(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Service
            SPL_S_OBJECTS,
            // Requested Function
            SPL_F_LIST,
            // Action Description Translator Tag
            "Read Objects List",
            // Request Parameters Array
            array(
                "type" => $objectType,
                "filters" => $filter,
                "params" => $params,
            )
        );

        return Webservice::extractArray($task)?: array();
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdByPrimary(string $objectType, array $keys): ?string
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest() || empty($objectType)) {
            return null;
        }
        //====================================================================//
        // Execute Generic WebService Action
        $task = $this->doGeneric(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Service
            SPL_S_OBJECTS,
            // Requested Function
            SPL_F_IDENTIFY,
            // Action Description Translator Tag
            "Identify Object by Primary Keys",
            // Request Parameters Array
            array(
                "type" => $objectType,
                "keys" => $keys,
            )
        );

        return Webservice::extractString($task) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getObject(string $objectType, $objectIds, array $fieldsList): ?array
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return null;
        }
        //====================================================================//
        // Safety Checks
        if (empty($objectType)) {
            return null;
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
        return $this->doComboRead(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Parameters Array
            $parameters
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setObject(string $objectType, string $objectId = null, array $objectData = array()): ?string
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return null;
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
        $task = $this->doGeneric(
            $this->getConfiguration(),          // WebService Configuration
            SPL_S_OBJECTS,               // Request Service
            SPL_F_SET,                    // Requested Function
            "Write Object Data",           // Action Description Translator Tag
            $parameters                         // Request Parameters Array
        );
        //====================================================================//
        // PhpUnit Helper => Submit Object Commit
        if (!empty($task)) {
            $action = empty($objectId) ? SPL_A_CREATE : SPL_A_UPDATE;
            if (Splash::isDebugMode()) {
                TestHelper::simObjectCommit(
                    $objectType,
                    (string) Webservice::extractString($task),
                    $action
                );
            }
        }

        return Webservice::extractString($task);
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
        // Execute Generic WebService Action
        $task = $this->doGeneric(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Service
            SPL_S_OBJECTS,
            // Requested Function
            SPL_F_DEL,
            // Action Description Translator Tag
            "Delete Object",
            // Request Parameters Array
            array(
                "type" => $objectType,
                "id" => $objectId,
            )
        );
        $response = !empty(Webservice::extractString($task));
        //====================================================================//
        // PhpUnit Helper => Submit Object Commit
        if ($response) {
            if (Splash::isDebugMode()) {
                TestHelper::simObjectCommit($objectType, $objectId, SPL_A_DELETE);
            }
        }

        return $response;
    }

    //====================================================================//
    // Files Interfaces
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function getFile(string $filePath, string $fileMd5): ?array
    {
        //====================================================================//
        // Safety Check => Verify Self-test Pass
        if (!$this->selfTest()) {
            return null;
        }
        //====================================================================//
        // Safety Checks
        if (empty($filePath) || empty($fileMd5)) {
            return null;
        }
        //====================================================================//
        // Execute Generic WebService Action
        $task = $this->doGeneric(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Service
            SPL_S_FILE,
            // Requested Function
            SPL_F_GETFILE,
            // Action Description Translator Tag
            "Read File",
            // Request Parameters Array
            array(
                "path" => $filePath,
                "md5" => $fileMd5,
            )
        );

        return Webservice::extractArray($task);
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
        // Safety Check => Verify Self-test Pass
        if (!$this->selfTest()) {
            return array();
        }
        //====================================================================//
        // Execute Generic WebService Action
        $task = $this->doGeneric(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Service
            SPL_S_WIDGETS,
            // Requested Function
            SPL_F_WIDGET_LIST,
            // Action Description Translator Tag
            "Read Widgets List"
        );

        return Webservice::extractArray($task) ?? array();
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetDescription(string $widgetType) : array
    {
        //====================================================================//
        // Safety Check => Verify Self-test Pass
        if (!$this->selfTest()) {
            return array();
        }
        //====================================================================//
        // Execute Generic WebService Action
        $task = $this->doGeneric(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Service
            SPL_S_WIDGETS,
            // Requested Function
            SPL_F_WIDGET_DEFINITION,
            // Action Description Translator Tag
            "Read Widget Definition",
            // Request Parameters Array
            array(
                "type" => $widgetType,
            )
        );

        return Webservice::extractArray($task) ?? array();
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetContents(string $widgetType, array $params = array()): ?array
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return null;
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
        // Execute Generic WebService Action
        $task = $this->doGeneric(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Service
            SPL_S_WIDGETS,
            // Requested Function
            SPL_F_WIDGET_GET,
            // Action Description Translator Tag
            "Read Widget Contents",
            // Request Parameters Array
            array(
                "type" => $widgetType,
                "params" => $params,
            )
        );

        return Webservice::extractArray($task);
    }

    //====================================================================//
    // Profile Interfaces
    //====================================================================//

    /**
     * Get Connector Profile Information
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
    public function getMasterAction(): ?string
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
     * @return null|array
     */
    private function doGeneric(
        array $config,
        string $service,
        string $action,
        string $desc,
        array $params = array()
    ): ?array {
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
        if (empty($response['result']) || empty($response['tasks']) || !is_array($response['tasks'])) {
            return null;
        }
        //====================================================================//
        // Get Next Task Result
        $task = array_shift($response['tasks']);
        //====================================================================//
        // Return Task Data
        return is_array($task) ? $task : null;
    }

    /**
     * Perform Multiple Soap Action
     *
     * @param array $config     WebService Configuration
     * @param array $parameters Array of Action Parameters.
     *
     * @return null|array
     */
    private function doComboRead(
        array $config,
        array $parameters = array()
    ): ?array {//====================================================================//
        // Create Webservice Component
        $webservice = (new Webservice())->configure($config);
        //====================================================================//
        // Add Task To Queue
        foreach ($parameters as $params) {
            $webservice->addTask(SPL_F_GET, $params, "Read Object Data");
        }
        //====================================================================//
        // Perform Request
        $response = $webservice->call(SPL_S_OBJECTS);
        //====================================================================//
        // Verify Response is Ok
        if (empty($response['result']) || empty($response['tasks']) || !is_array($response['tasks'])) {
            return null;
        }
        //====================================================================//
        // Get Tasks Results
        $results = array();
        foreach ($response['tasks'] as $task) {
            $results[] = Webservice::extractArray(is_array($task) ? $task : null);
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
     * @return null|array
     */
    private function getOneObject(string $objectType, string $objectId, array $fieldsList): ?array
    {
        //====================================================================//
        // Execute Generic WebService Action
        $task = $this->doGeneric(
            // WebService Configuration
            $this->getConfiguration(),
            // Request Service
            SPL_S_OBJECTS,
            // Requested Function
            SPL_F_GET,
            // Action Description Translator Tag
            "Read Object Data",
            // Request Parameters Array
            array(
                "type" => $objectType,
                "id" => $objectId,
                "fields" => $fieldsList,
            )
        );

        return Webservice::extractArray($task);
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
        if (empty($config['WsIdentifier'])) {
            return Splash::log()->err('ErrWsNoId');
        }

        //====================================================================//
        // Verify Server Key not empty
        if (empty($config['WsEncryptionKey'])) {
            return Splash::log()->err('ErrWsNoKey');
        }

        //====================================================================//
        // Verify host address is present
        if (empty($config['WsHost'])) {
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
            if (empty($config['HttpUser'])) {
                return Splash::log()->err('ErrWsNoHttpUser');
            }
            if (empty($config['HttpPassword'])) {
                return Splash::log()->err('ErrWsNoHttpPwd');
            }
        }

        return true;
    }
}

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

namespace Splash\Connectors\Soap\Services;

use ArrayObject;
use Exception;
use Splash\Bundle\Events\ActionsListingEvent;
use Splash\Bundle\Events\ObjectsListingEvent;
use Splash\Bundle\Form\StandaloneFormType;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Connectors\Soap\Componants\Webservice;
use Splash\Core\SplashCore as Splash;
use Splash\Models\AbstractObject;

//use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @abstract Splash Soap Connector
 */
final class SoapConnector extends AbstractConnector
{
//    use ContainerAwareTrait;
    
    /**
     * {@inheritdoc}
     */
    public function ping() : bool
    {
        //====================================================================//
        // Create Webservice Componant
        $Webservice =   (new Webservice())->configure($this->getConfiguration());
        //====================================================================//
        // Perform Ping Test
        $Response = $Webservice->call(SPL_S_PING, null, true);
        if (isset($Response["result"]) && !empty($Response["result"])) {
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
        // Create Webservice Componant
        $Webservice =   (new Webservice())->configure($this->getConfiguration());
        //====================================================================//
        // Perform Connect Test
        $Response = $Webservice->call(SPL_S_CONNECT);
        if (isset($Response["result"]) && !empty($Response["result"])) {
            return true;
        }

        return false;
    }
        
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function informations(ArrayObject  $Informations) : ArrayObject
    {
        //====================================================================//
        // Execute Generic WebService Action
        $Response   =   $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_ADMIN,                                  // Request Service
                SPL_F_GET_INFOS,                                // Requested Function
                "Read Server Infos"                             // Action Description Translator Tag
            );
        //====================================================================//
        // Check Response
        if (!$Response) {
            return $Informations;
        }
        //====================================================================//
        // Import Response Object
        foreach ($Response as $Key => $Value) {
            $Informations->offsetSet($Key, $Value);
        }

        return $Informations;
    }
    
    /**
     * {@inheritdoc}
     */
    public function selfTest() : bool
    {
        //====================================================================//
        // Execute Generic WebService Action
        return $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_ADMIN,                                    // Request Service
                SPL_F_GET_SELFTEST,                             // Requested Function
                "Read Server SelfTest"                          // Action Description Translator Tag
            );
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
        // Execute Generic WebService Action
        $response   =   $this->GenericAction(
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
    public function getObjectDescription(string $ObjectType) : array
    {
        //====================================================================//
        // Initiate Tasks parameters array
        $Parameters = array(
            "type"  =>  $ObjectType
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_OBJECTS,                                  // Request Service
                SPL_F_DESC,                                     // Requested Function
                "Read Object Description",                      // Action Description
                $Parameters                                     // Requets Parameters Array
            );
    }
      
    /**
     * {@inheritdoc}
     */
    public function getObjectFields(string $ObjectType) : array
    {
        //====================================================================//
        // Initiate Tasks parameters array
        $Parameters = array(
            "type"  =>  $ObjectType
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_OBJECTS,                                  // Request Service
                SPL_F_FIELDS,                                   // Requested Function
                "Read Object Fields",                           // Action Description Translator Tag
                $Parameters                                     // Requets Parameters Array
            );
    }
    
    public function getObjectList(string $ObjectType, string $Filter = null, array $Params = array()) : array
    {
        //====================================================================//
        // Initiate Tasks parameters array
        $Parameters = array(
            "type"      =>  $ObjectType,
            "filters"   =>  $Filter,
            "params"    =>  $Params
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_OBJECTS,                                  // Request Service
                SPL_F_LIST,                                     // Requested Function
                "Read Objects List",                            // Action Description Translator Tag
                $Parameters                                     // Requets Parameters Array
            );
    }
    
    /**
     * {@inheritdoc}
     */
    public function getObject(string $ObjectType, $ObjectIds, array $List)
    {
        //====================================================================//
        // Safety Checks
        if (empty($ObjectType)) {
            return false;
        }
        //====================================================================//
        // Take Care of Single Read Requests
        if (is_scalar($ObjectIds)) {
            return $this->getOneObject($ObjectType, $ObjectIds, $List);
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $Parameters = array();
        foreach ($ObjectIds as $ObjectId) {
            $Parameters[] = array(
                "type"      =>  $ObjectType,
                "id"        =>  $ObjectId,
                "fields"    =>  $List
            );
        }
        //====================================================================//
        // Execute Combo WebService Action
        return $this->ComboAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_OBJECTS,                                  // Request Service
                SPL_F_GET,                                      // Requested Function
                "Read Object Data",                             // Action Description Translator Tag
                $Parameters                                     // Requets Parameters Array
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setObject(string $ObjectType, string $ObjectId = null, array $Data = array())
    {
        //====================================================================//
        // Initiate Tasks parameters array
        $Parameters = array(
            "type"      =>  $ObjectType,
            "id"        =>  $ObjectId,
            "fields"    =>  $Data
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_OBJECTS,                                  // Request Service
                SPL_F_SET,                                      // Requested Function
                "Write Object Data",                            // Action Description Translator Tag
                $Parameters                                     // Requets Parameters Array
            );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteObject(string $ObjectType, string $ObjectId) : bool
    {
        //====================================================================//
        // Initiate Tasks parameters array
        $Parameters = array(
            "type"      =>  $ObjectType,
            "id"        =>  $ObjectId,
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_OBJECTS,                                  // Request Service
                SPL_F_DEL,                                      // Requested Function
                "Delete Object",                                // Action Description Translator Tag
                $Parameters                                     // Requets Parameters Array
            );
    }

    //====================================================================//
    // Files Interfaces
    //====================================================================//
    
    /**
     * {@inheritdoc}
     */
    public function getFile(string $Path, string $Md5)
    {
        //====================================================================//
        // Safety Checks
        if (empty($Path) || empty($Md5)) {
            return array();
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $Parameters = array(
            "path"      =>  $Path,
            "md5"       =>  $Md5,
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_FILE,                                     // Request Service
                SPL_F_GETFILE,                                  // Requested Function
                "Read File",                                    // Action Description Translator Tag
                $Parameters                                     // Requets Parameters Array
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
        // Execute Generic WebService Action
        $response = $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_WIDGETS,                                  // Request Service
                SPL_F_WIDGET_LIST,                              // Requested Function
                "Read Widgets List"                             // Action Description Translator Tag
            );
        
        return (false === $response) ? array() : $response;        
    }
    
    /**
     * {@inheritdoc}
     */
    public function getWidgetDescription(string $WidgetType) : array
    {
        //====================================================================//
        // Initiate Tasks parameters array
        $Parameters = array(
            "type"      =>  $WidgetType,
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_WIDGETS,                                  // Request Service
                SPL_F_WIDGET_DEFINITION,                        // Requested Function
                "Read Widget Definition",                       // Action Description Translator Tag
                $Parameters                                     // Requets Parameters Array
            );
    }
    
    /**
     * {@inheritdoc}
     */
    public function getWidgetContents(string $WidgetType, array $WidgetConfig = array())
    {    
        //====================================================================//
        // Convert Dates to Splash String Format
        if (isset($WidgetConfig["DateStart"]) && is_a($WidgetConfig["DateStart"], "DateTime")) {
            $WidgetConfig["DateStart"] = $WidgetConfig["DateStart"]->format(SPL_T_DATETIMECAST);
        }
        if (isset($WidgetConfig["DateEnd"]) && is_a($WidgetConfig["DateEnd"], "DateTime")) {
            $WidgetConfig["DateEnd"] = $WidgetConfig["DateEnd"]->format(SPL_T_DATETIMECAST);
        }
        //====================================================================//
        // Initiate Tasks parameters array
        $Parameters = array(
            "type"      =>  $WidgetType,
            "params"    =>  $WidgetConfig,
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_WIDGETS,                                  // Request Service
                SPL_F_WIDGET_GET,                               // Requested Function
                "Read Widget Contents",                         // Action Description Translator Tag
                $Parameters                                     // Requets Parameters Array
            );
    }
    
    //====================================================================//
    // Profile Interfaces
    //====================================================================//
    
    /**
     * @abstract   Get Connector Profile Informations
     * @return  array
     */
    public function getProfile() : array
    {
        return array(
            'enabled'   =>      true,                                   // is Connector Enabled
            'beta'      =>      true,                                   // is this a Beta release
            'type'      =>      self::TYPE_SERVER,                      // Connector Type or Mode
            'name'      =>      'soap',                                 // Connector code (lowercase, no space allowed)
            'connector' =>      'splash.connectors.soap',               // Connector PUBLIC service
            'title'     =>      'Soap Connector',                       // Public short name
            'label'     =>      'The Soap Connector',                   // Public long name
            'domain'    =>      false,                                  // Translation domain for names
            'ico'       =>      'bundles/splash/splash-ico.png',        // Public Icon path
            'www'       =>      'www.splashsync.com',                   // Website Url
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function getConnectedTemplate() : string
    {
        return "@Splash/profile/profile.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getOfflineTemplate() : string
    {
        return "@Splash/profile/profile.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getNewTemplate() : string
    {
        return "@Splash/profile/profile.html.twig";
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFormBuilderName() : string
    {
        return StandaloneFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableActions() : array
    {
//        //====================================================================//
//        // Dispatch Object Listing Event
//        $Event  =   $this->getEventDispatcher()->dispatch(ActionsListingEvent::NAME, new ActionsListingEvent());
//        //====================================================================//
//        // Return Actions Types Array
//        return $Event->getAll();
    }
    
    //====================================================================//
    //  HIGH LEVEL WEBSERVICE CALLS
    //====================================================================//
    
    /**
     * @abstract   Perform Generic Soap Action
     *
     * @param      array    $Config             WebService Configuration
     * @param      string   $Service            Service Method to reach
     * @param      string   $Action             Service Action to perform
     * @param      string   $Description        Action Description for Information
     * @param      array    $Parameters         Action Parameters.
     *
     * @return     mixed
     */
    protected function GenericAction(array $Config, string $Service, string $Action, string $Description, array $Parameters = array())
    {
        //====================================================================//
        // Create Webservice Componant
        $Webservice =   (new Webservice())->configure($Config);
        //====================================================================//
        // Add Task To Queue
        $Webservice->addTask($Action, $Parameters, $Description);
        //====================================================================//
        // Perform Request
        $Response   =   $Webservice->call($Service);
        //====================================================================//
        // Verify Response is Ok
        if (!isset($Response->result) || empty($Response->result) || empty($Response->tasks)) {
            return false;
        }
        //====================================================================//
        // Get Next Task Result
        $Tasks = $Response->tasks->getArrayCopy();
        $Task = array_shift($Tasks);
        //====================================================================//
        // Return Task Data
        return Webservice::extractData($Task);
    }
    
    /**
     * @abstract   Perform Multiple Soap Action
     *
     * @param      array    $Config             WebService Configuration
     * @param      string   $Service            Service Method to reach
     * @param      string   $Action             Service Action to perform
     * @param      string   $Description        Action Description for Information
     * @param      array    $Parameters         Array of Action Parameters.
     *
     * @return     mixed
     */
    protected function ComboAction(array $Config, string $Service, string $Action, string $Description, array $Parameters = array())
    {
        //====================================================================//
        // Create Webservice Componant
        $Webservice =   (new Webservice())->configure($Config);
        //====================================================================//
        // Add Task To Queue
        foreach ($Parameters as $Params) {
            $Webservice->addTask($Action, $Params, $Description);
        }
        //====================================================================//
        // Perform Request
        $Response   =   $Webservice->call($Service);
        //====================================================================//
        // Verify Response is Ok
        if (!isset($Response->result) || empty($Response->result) || empty($Response->tasks)) {
            return false;
        }
        //====================================================================//
        // Get Tasks Results
        $Results = array();
        foreach ($Response->tasks as $Task) {
            $Results[]   =   Webservice::extractData($Task);
        }
        //====================================================================//
        // Return Tasks Results
        return   $Results;
    }
    
    /**
     * {@inheritdoc}
     */
    private function getOneObject(string $ObjectType, string $ObjectId, array $List)
    {
        //====================================================================//
        // Initiate Tasks parameters array
        $Parameters = array(
            "type"      =>  $ObjectType,
            "id"        =>  $ObjectId,
            "fields"    =>  $List
        );
        //====================================================================//
        // Execute Generic WebService Action
        return $this->GenericAction(
                $this->getConfiguration(),                      // WebService Configuration
                SPL_S_OBJECTS,                                  // Request Service
                SPL_F_GET,                                      // Requested Function
                "Read Object Data",                             // Action Description Translator Tag
                $Parameters                                     // Requets Parameters Array
            );
    }
}

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

namespace Splash\Connectors\Soap\Controller;

use Exception;
use SoapServer;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Client\Splash;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Splash Bundle Soap Controller
 */
class SoapController extends AbstractController
{
    /**
     * @var AbstractConnector
     */
    protected AbstractConnector $connector;

    /**
     * @var SoapServer
     */
    private SoapServer $soapServer;

    /**
     * @var null|array Decoded Client Message
     */
    private ?array $inputs;

    /**
     * @var array[] Clients Tasks Buffer
     */
    private array $tasks;

    //====================================================================//
    //   WebService SOAP Calls Responses Functions
    //====================================================================//

    /**
     * Master Fake Controller Action
     *
     * @param AbstractConnector $connector
     *
     * @return Response
     */
    public function masterAction(AbstractConnector $connector): Response
    {
        //====================================================================//
        // Store Soap Connector
        $this->connector = $connector;
        //====================================================================//
        // Create SOAP Server
        $this->soapServer = new SoapServer(
            dirname(__DIR__).'/Resources/wsdl/splash.wsdl',
            array('cache_wsdl' => WSDL_CACHE_NONE)
        );
        //====================================================================//
        // Register SOAP Service
        $this->soapServer->setObject($this);
        //====================================================================//
        // Register shutdown method available for fatal errors retrieval
        register_shutdown_function(array(self::class, 'fatalHandler'));
        //====================================================================//
        // Prepare Response
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');
        //====================================================================//
        // Execute Actions
        ob_start();
        $this->soapServer->handle();
        $response->setContent(ob_get_clean());
        //====================================================================//
        // Return response
        return $response;
    }

    /**
     * Declare fatal Error Handler => Called in case of Script Exceptions
     */
    public static function fatalHandler(): void
    {
        //====================================================================//
        // Read Last Error
        $error = error_get_last();
        if (!$error) {
            return;
        }
        //====================================================================//
        // Non Fatal Error
        if (E_ERROR != $error['type']) {
            Splash::log()->war($error['message'].' on File '.$error['file'].', Line '.$error['line']);

            return;
        }

        //====================================================================//
        // Fatal Error
        //====================================================================//

        //====================================================================//
        // Parse Error in Response.
        Splash::com()->fault($error);
        //====================================================================//
        // Process methods & Return the results.
        Splash::com()->handle();
    }

    //====================================================================//
    //   WebService Available Services
    //====================================================================//

    /**
     * Minimal Test of WebService connection. No Encryption, Just Verify Node is found.
     *
     * @param string $identifier WebService Remote Node Identifier
     *
     * @return null|string
     */
    public function ping(string $identifier): ?string
    {
        //====================================================================//
        // Perform Identify Pointed Server
        $result = $this->doIdentify($identifier);
        //====================================================================//
        // Server Not Found
        if (!$result) {
            $this->soapServer->fault("0", '[Splash] Ping Fail. Unable to identify this remote Node');

            return null;
        }
        //====================================================================//
        // Add Success Message
        Splash::log()->msg('Ping Successful. Hello '.Splash::configuration()->localname.' !!');

        //====================================================================//
        // Transmit Answer with No Encryption
        return Splash::ws()->pack(array('result' => true, 'log' => Splash::log()), true);
    }

    /**
     * Splash SOAP Connect Action
     *
     * @param string $webserviceId
     * @param string $data
     *
     * @return null|string
     */
    public function connect(string $webserviceId, string $data): ?string
    {
        //====================================================================//
        // Receive Request from Client
        //====================================================================//
        if (!$this->doReceive($webserviceId, $data)) {
            return $this->doTransmit(false);
        }

        //====================================================================//
        // Update Server Status
        //====================================================================//
        $config = $this->connector->getConfiguration();
        if (empty($config['WsHost']) && isset($this->inputs['server'])) {
            $serverInfos = $this->inputs['server'];
            //====================================================================//
            // Update Server Path
            if (!empty($serverInfos['ServerPath']) && is_scalar($serverInfos['ServerPath'])) {
                $this->connector->setParameter('WsPath', $serverInfos['ServerPath']);
                Splash::log()->msg('Server Path Updated to "'.$serverInfos['ServerPath'].'"');
            }
            //====================================================================//
            // Update Server Host
            if (!empty($serverInfos['ServerHost']) && is_scalar($serverInfos['ServerHost'])) {
                Splash::log()->msg('HostName Updated to "'.$serverInfos['ServerHost'].'"');
                $this->connector->setParameter('WsHost', $serverInfos['ServerHost']);
                $this->connector->updateConfiguration();
            }
        }

        //====================================================================//
        // Log Request
        //====================================================================//
        if (!isset($this->inputs['cfg']['silent'])) {
            Splash::log()->msg('Connection Successful. Hello '.Splash::configuration()->localname.'!!');
        }

        //====================================================================//
        // Transmit Answers To Client
        //====================================================================//
        return $this->doTransmit(true);
    }

    /**
     * Splash SOAP Object Action
     *
     * @param string $webserviceId
     * @param string $data
     *
     * @return string
     */
    public function objects(string $webserviceId, string $data): string
    {
        return $this->doTasks($webserviceId, $data, array(SPL_F_COMMIT));
    }

    /**
     * Splash SOAP File Action
     *
     * @param string $webserviceId
     * @param string $data
     *
     * @return string
     */
    public function files(string $webserviceId, string $data): string
    {
        return $this->doTasks($webserviceId, $data, array(SPL_F_GETFILE));
    }

    //====================================================================//
    //   WebService Low Level Functions
    //====================================================================//

    /**
     * Identify & Initialize Server before request execution.
     *
     * @param string $webserviceId
     *
     * @return null|bool
     */
    private function doIdentify(string $webserviceId) : ?bool
    {
        //====================================================================//
        // Perform Identify Pointed Server
        $result = $this->connector->identify($webserviceId);
        //====================================================================//
        // Server Found
        if (!$result) {
            return $result;
        }
        //====================================================================//
        // Reboot Splash Core Module
        Splash::reboot();
        //====================================================================//
        // Force Splash Logger Prefix
        Splash::log()->setPrefix("Splash");
        //====================================================================//
        // Configure Webservice Component with Minimal Parameters
        Splash::configuration()->localname = $this->connector->getParameter("Name");
        Splash::configuration()->WsIdentifier = $this->connector->getParameter("WsIdentifier");
        Splash::configuration()->WsEncryptionKey = $this->connector->getParameter("WsEncryptionKey");
        //====================================================================//
        // Clean Logger due to Splash::configuration() with Empty Parameters
        Splash::log()->cleanLog();
        //====================================================================//
        // Add Identify Debug Message
        Splash::log()->deb('Hello '.Splash::configuration()->localname.' !!');

        return $result;
    }

    /**
     * Treat Received Data and Initialize Server before request execution.
     *
     * @param string $webserviceId
     * @param string $data         Received Raw Data
     *
     * @return bool
     */
    private function doReceive(string $webserviceId, string $data): bool
    {
        //====================================================================//
        // Perform Identifier Verification
        $identify = $this->doIdentify($webserviceId);
        //====================================================================//
        // Server Not Found
        if (false === $identify) {
            $this->connector->getLogger()->warning(
                '[SoapConnector]'.'::'.__FUNCTION__.' request received from '
                .$webserviceId.'(Unknown!) =>  Connection Refused'
            );

            $this->soapServer->fault("0", '[Splash]  Connection Refused');

            return false;
        }
        //===================================================================//
        // Server Connection Rejected
        if (null === $identify) {
            Splash::log()->err('Connection Refused. This Server Is Disabled.');

            return false;
        }
        //====================================================================//
        // Unpack SOAP Request
        $this->inputs = Splash::ws()->unpack($data);
        if (false == $this->inputs) {
            Splash::log()->err('Connection Refused. This Server Is Disabled.');

            return false;
        }
        Splash::log()->deb('[Splash] Node Identified & Message Decoded.');

        //====================================================================//
        // Extract Received Tasks
        if (!empty($this->inputs['tasks'])) {
            $this->tasks = $this->inputs['tasks'];
            Splash::log()->deb('Found '.count($this->tasks).' tasks in request.');
        }

        return true;
    }

    /**
     * Return packaged data buffer for transmit to client.
     *
     * @param bool       $result Global Operation Result
     * @param null|array $tasks  Tasks Response Data
     *
     * @return string
     */
    private function doTransmit(bool $result, array $tasks = null): string
    {
        return (string) Splash::ws()->pack(array(
            'result' => $result,
            'tasks' => $tasks,
            'log' => Splash::log(),
        ));
    }

    /**
     * Execute Request Tasks from Client Remote Server
     *
     * @param string $identifier WebService Remote Webservice Identifier
     * @param string $data       WebService Packaged Data Inputs
     * @param array  $filters    WebService Allowed Actions
     *
     * @return string
     */
    private function doTasks(string $identifier, string $data, array $filters = array()): string
    {
        //====================================================================//
        // Validate & Unpack received Request
        if (!$this->doReceive($identifier, $data)) {
            return $this->doTransmit(false);
        }
        //====================================================================//
        // Execute Tasks
        //====================================================================//
        if (empty($this->tasks)) {
            Splash::log()->err("No Task found in request.");

            return $this->doTransmit(true);
        }
        //====================================================================//
        // Init Tasks Responses Array
        $response = array();
        //====================================================================//
        // Step by Step Execute Tasks
        foreach ($this->tasks as $id => $task) {
            //====================================================================//
            // Safety Check Skip Empty Tasks
            if (empty($task)) {
                continue;
            }
            //====================================================================//
            // Check if Tasks is Allowed in this Service
            if (!empty($filters) && !in_array($task["name"], $filters, true)) {
                Splash::log()->err("Requested task was not found => ".$task["name"]." (".$task["desc"].")");
                $response[$id] = $this->createEmptyTaskResponse($task["id"], $task["name"], $task["desc"]);

                continue;
            }

            switch ($task["name"]) {
                //====================================================================//
                // Execute Object Commit
                case SPL_F_COMMIT:
                    $response[$id] = $this->objectCommit($task);

                    break;
                //====================================================================//
                // Execute Object Commit
                case SPL_F_GETFILE:
                    $response[$id] = $this->fileRead($task);

                    break;
            }
        }

        //====================================================================//
        // Transmit Answers To Master
        //====================================================================//
        return $this->doTransmit(true, $response);
    }

    /**
     * Create An Empty Task Response
     *
     * @param string $taskId   Task Id
     * @param string $taskName Task Name
     * @param string $desc     Task Description
     *
     * @return array
     */
    private function createEmptyTaskResponse(string $taskId, string $taskName, string $desc = "") : array
    {
        return array(
            "id" => $taskId,
            "name" => $taskName,
            "desc" => $desc,
            "result" => false,
        );
    }

    /**
     * Manage Object Commit Notification
     *
     * @param array $task Full Task Request
     *
     * @return array
     */
    private function objectCommit(array $task) : array
    {
        Splash::log()->deb("Objects Router - Execute Action => ".$task['name']." (".$task['desc'].")");

        //====================================================================//
        // Init Tasks results array
        $response = $this->createEmptyTaskResponse($task['id'], $task['name'], $task['desc']);

        try {
            //====================================================================//
            // Dispatch Commit Event
            $this->connector->commit(
                $task['params']['type'],                    // Object Type
                $task['params']['id'],                      // Object ID or Objects ID Array
                $task['params']['action'],                  // Action Type
                $task['params']['user'],                    // User Name
                $task['params']['comment']                  // Action Description or Comment
            );
            $response['result'] = true;
            //====================================================================//
            // If Commit Notification Successful => Add Ok Message
            Splash::log()->msg("Change Notified!");
        } catch (Exception $ex) {
            Splash::log()->err("Commit Fail => ".$ex->getMessage().")");
        }

        return $response;
    }

    /**
     * Manage Files Reading
     *
     * @param array $task Full Task Request
     *
     *  @return array
     */
    private function fileRead(array $task) : array
    {
        Splash::log()->deb("Files Router - Execute Action => ".$task['name']." (".$task['desc'].")");
        //====================================================================//
        // Init Tasks results array
        $response = $this->createEmptyTaskResponse($task['id'], $task['name'], $task['desc']);

        try {
            //====================================================================//
            // Dispatch File Event
            $response['data'] = $this->connector->file(
                // File Path
                $task['params']['file'],
                // File MD5 Checksum
                $task['params']['md5']
            );
            $response['result'] = true;
        } catch (Exception $ex) {
            Splash::log()->err("Read File Fail => ".$ex->getMessage().")");
        }

        return $response;
    }
}

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

namespace Splash\Connectors\Soap\Controller;

use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Local\ActionsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @abstract    Splash Faker Connector Actions Controller
 */
class ActionsController extends Controller
{
    use ActionsTrait;
    
    /**
     * @abstract    Ask for Server Host Refresh
     *
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return Response
     */
    public function hostAction(Request $request, AbstractConnector $connector)
    {
        //====================================================================//
        // Safety Check => Action Only Available in Extended Mode
        if ($connector->getParameter('Extended', false)) {
            //====================================================================//
            // Clear Host & Path
            $connector->setParameter('WsHost', null);
            $connector->setParameter('WsPath', null);
            
            //====================================================================//
            // Update Configuration
            $connector->updateConfiguration();
        }
        //====================================================================//
        // Redirect Response
        /** @var string $referer */
        $referer = $request->headers->get('referer');
        if (empty($referer)) {
            return self::getDefaultResponse();
        }

        return new RedirectResponse($referer);
    }
    
    /**
     * @abstract    Ask for Server Keys Refresh
     *
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return Response
     */
    public function keysAction(Request $request, AbstractConnector $connector)
    {
        //====================================================================//
        // Safety Check => Action Only Available in Extended Mode
        if ($connector->getParameter('Extended', false)) {
            //====================================================================//
            // Generate Unique Identifier
            $connector->setParameter('WsIdentifier', substr(md5(uniqid((string) time(), true)), 0, 16));

            //====================================================================//
            // Generate Ws Key
            $connector->setParameter('WsEncryptionKey', substr(hash("sha256", time().uniqid("", true)), 0, 50));
            
            //====================================================================//
            // Update Configuration
            $connector->updateConfiguration();
        }
        //====================================================================//
        // Redirect Response
        /** @var string $referer */
        $referer = $request->headers->get('referer');
        if (empty($referer)) {
            return self::getDefaultResponse();
        }

        return new RedirectResponse($referer);
    }
}

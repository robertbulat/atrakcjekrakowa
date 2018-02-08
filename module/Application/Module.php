<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;
use Application\Model\Auth;
use Application\Model\UsersTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

// Add this for SMTP transport
use Zend\ServiceManager\ServiceManager;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\Mvc\ModuleRouteListener;

/** @noinspection PhpInconsistentReturnPointsInspection */
class Module implements BootstrapListenerInterface
{
    const VERSION = '3.0.3-dev';

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
     * @return array
     */

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function onBootstrap(EventInterface $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

//        $e ->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e)
//        {
//            $controller = $e->getTarget();
//            $controllerClass = get_class($controller);
//            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
//            $config = $e->getApplication()->getServiceManager()->get('config');
//            $action = $e->getRouteMatch()->getParam('action');
//
//            if (isset($config['module_layouts'][$action])):
//                $controller->layout($config['module_layouts'][$action]);
//
//            elseif (isset($config['module_layouts'][$moduleNamespace])):
//                $controller->layout($config['module_layouts'][$moduleNamespace]);
//            endif;
//        } , 100);

    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                // For Yable data Gateway
                'Application\Model\UsersTable' =>  function($sm) {
                    $tableGateway = $sm->get('UsersTableGateway');
                    $table = new UsersTable($tableGateway);
                    return $table;
                },
                'UsersTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Auth()); // Notice what is set here
                    return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
                },
                // Add this for SMTP transport
                'mail.transport' => function (ServiceManager $serviceManager) {
                    $config = $serviceManager->get('Config');
                    $transport = new Smtp();
                    $transport->setOptions(new SmtpOptions($config['mail']['transport']['options']));
                    return $transport;
                },
            ),
        );
    }

}

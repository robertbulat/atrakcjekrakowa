<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'module_layouts' =>
        [
            'Application' => 'layout/layout'
        ],
    'db' => array(
        'driver' => 'Pdo',
        'dsn' => 'mysql:dbname=x; host=x',
        'user' => 'x',
        'password' => 'x'
    ),

    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    )


);


<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;

use Zend\Db\Adapter\Adapter as DbAdapter;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;

use Application\Model\Auth;
use Application\Form\AuthForm;

class LayoutController extends AbstractActionController
{
    public function layoutAction()
    {
        $auth = new AuthenticationService();
        // or prepare in the globa.config.php and get it from there
        // $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');

        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
        }

    }
}
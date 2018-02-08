<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter;

use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;

use Zend\Db\Adapter\Adapter as DbAdapter;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;

use Application\Model\Auth;
use Application\Form\AuthForm;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
		return new ViewModel();
	}
	
    public function loginAction()
	{
	    $user = $this->identity();
		$form = new AuthForm();
		$form->get('submit')->setValue('Zaloguj');
        $messages = null;


		$request = $this->getRequest();
        if ($request->isPost()) {
			$authFormFilters = new Auth();
            $form->setInputFilter($authFormFilters->getInputFilter());	
			$form->setData($request->getPost());
			 if ($form->isValid()) {
				$data = $form->getData();
				$sm = $this->getServiceLocator();
				$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
				
				$config = $this->getServiceLocator()->get('Config');
				$staticSalt = $config['static_salt'];

				$authAdapter = new AuthAdapter($dbAdapter,
										   'users', // there is a method setTableName to do the same
										   'usr_name', // there is a method setIdentityColumn to do the same
										   'usr_password', // there is a method setCredentialColumn to do the same
//     "MD5(CONCAT('$staticSalt', ?, usr_password_salt))"
                                          "MD5(CONCAT('$staticSalt', ?, usr_password_salt )) AND usr_active = 1" // setCredentialTreatment(parametrized string) 'MD5(?)'
										  );
				//  70bb9e0b4a07e8ee00274d2fdd59403b


				$authAdapter
					->setIdentity($data['usr_name'])
					->setCredential($data['usr_password'])
				;
				
				$auth = new AuthenticationService();
				// or prepare in the globa.config.php and get it from there. Better to be in a module, so we can replace in another module.
				// $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
				// $sm->setService('Zend\Authentication\AuthenticationService', $auth); // You can set the service here but will be loaded only if this action called.
				$result = $auth->authenticate($authAdapter);			
				
				switch ($result->getCode()) {
					case Result::FAILURE_IDENTITY_NOT_FOUND:
						break;

					case Result::FAILURE_CREDENTIAL_INVALID:
                        break;
					case Result::SUCCESS:
						$storage = $auth->getStorage();
						$storage->write($authAdapter->getResultRowObject(
							null,
							'usr_password'
						));
						$time = 1209600; // 14 days 1209600/3600 = 336 hours => 336/24 = 14 days
//						if ($data['rememberme']) $storage->getSession()->getManager()->rememberMe($time); // no way to get the session
						if ($data['rememberme']) {
							$sessionManager = new \Zend\Session\SessionManager();
							$sessionManager->rememberMe($time);
						}
						break;

					default:
						// do stuff for other failure
						break;
				}				
				foreach ($result->getMessages() as $message) {
					$messages .= "$message\n"; 
				}
			 }
        }
		return new ViewModel(array('form' => $form, 'messages' => $messages));
	}
	
	public function logoutAction()
	{
		$auth = new AuthenticationService();
		// or prepare in the globa.config.php and get it from there
		// $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
		
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
		}			
		
		$auth->clearIdentity();
//		$auth->getStorage()->session->getManager()->forgetMe(); // no way to get the sessionmanager from storage
		$sessionManager = new \Zend\Session\SessionManager();
		$sessionManager->forgetMe();
		
		return $this->redirect()->toRoute('application/default', array('action' => ''));
	}	
}
<?php
namespace Auth\Controller;

use Auth\Form\ForgottenPasswordChangeFilter;
use Auth\Form\ForgottenPasswordChangeForm;
use Zend\Json;
use Auth\Model\UsersTable;
use Zend\Mime\Mime;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Auth\Model\Auth;
use Auth\Form\RegistrationForm;
use Auth\Form\RegistrationFilter;

use Auth\Form\ForgottenPasswordForm;
use Auth\Form\ForgottenPasswordFilter;
// a test class in a coolcsn namespace for installer. You can remove the next line
use CsnBase\Zend\Validator\ConfirmPassword;

use Zend\Mail\Message;

use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class RegistrationController extends AbstractActionController
{
    protected $usersTable;
    protected $usernameAjaxSearch;

    public function indexAction()
    {
        $form = new RegistrationForm();
        $form->get('submit')->setValue('Zarejestruj się');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter(new RegistrationFilter($this->getServiceLocator()));
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $data = $this->prepareData($data);
                $auth = new Auth();
                $auth->exchangeArray($data);
                $this->getUsersTable()->saveUser($auth);

                $this->sendConfirmationEmail($auth);
                $this->flashMessenger()->addMessage($auth->usr_email);
                return $this->redirect()->toRoute('auth/default', array('controller' => 'registration', 'action' => 'registration-success'));
            }
        }
        return new ViewModel(array('form' => $form));
    }

    public function registrationSuccessAction()
    {
        $usr_email = null;
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            foreach ($flashMessenger->getMessages() as $key => $value) {
                $usr_email .= $value;
            }
        }
        return new ViewModel(array('usr_email' => $usr_email));
    }

    public function usernameSearchAjaxAction()
    {
        #read the data sent from the site

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                $username_value = $this->getRequest()->getPost('username');
                $users = $this->getUsersTable()->getUserByUsername($username_value);
            }
        } else {

            //

        }
        $vm = new JsonModel(array(
            'users' => $users,
        ));
        return $vm;
    }

    public function emailaddressSearchAjaxAction()
    {

        #read the data sent from the site

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                $email_value = $this->getRequest()->getPost('emailaddress');
                $addresses = $this->getUsersTable()->getUserByEmailAddress($email_value);
            }
        } else {

            //

        }
        $vm = new JsonModel(array(
            'addresses' => $addresses,
        ));
        return $vm;
    }

    public function confirmEmailAction()
    {
        $token = $this->params()->fromRoute('id');
        $viewModel = new ViewModel(array('token' => $token));
        try {
            $user = $this->getUsersTable()->getUserByToken($token);
            $usr_id = $user->usr_id;
            $usr_name = $user->usr_name;
            $this->getUsersTable()->activateUser($usr_id);
        } catch (\Exception $e) {
            $viewModel->setTemplate('auth/registration/confirm-email-error.phtml');
        }
        return $viewModel;
    }

    public function forgottenPasswordAction()
    {
        $form = new ForgottenPasswordForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter(new ForgottenPasswordFilter($this->getServiceLocator()));
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $token = md5(uniqid(mt_rand(), true)); // $this->generateDynamicSalt();
                $usr_email = $data['usr_email'];
                $usersTable = $this->getUsersTable();
                $user = $usersTable->getUserByEmailAddress($usr_email);
                $username = $user->usr_name;

                $usersTable->savePasswordToken($token, $usr_email);
                $user->usr_password = $this->encriptPassword($this->getStaticSalt(), $password, $auth->usr_password_salt);

                $usersTable->activatePasswordToken($user->usr_id);
                $this->sendPasswordTokenByEmail($usr_email, $token, $username);
                $this->flashMessenger()->addMessage($usr_email);
                return $this->redirect()->toRoute('auth/default', array('controller' => 'registration', 'action' => 'password-send-success'));
            }
        }
        return new ViewModel(array('form' => $form));
    }

    public function forgottenPasswordChangeAction()
    {
        $request = $this->getRequest();
        $form = new ForgottenPasswordChangeForm();
        $usersTable = $this->getUsersTable();
        $token = $this->params()->fromRoute('id');
        $userexist = $usersTable->getUserByPasswordToken($token);


        if($request->isGet()) {
            if ($userexist->usr_passwordchangetoken_active == 1)
            {
                $form->get('usr_token')->setValue($token);
            return new ViewModel(array('form' => $form));
            }
            else
            {
                return $this->redirect()->toRoute('auth/default', array('controller' => 'registration', 'action' => 'forgotten-password-change-deactivated'));
            }
        }


        if ($request->isPost() ) {
            //  $form->setInputFilter(new ForgottenPasswordChangeFilter($this->getServiceLocator()));
            $form->setData($request->getPost());
            if ($form->isValid()) {

                        $data = $form->getData();
                        $usr_token = $form->get('usr_token')->getValue();
                        $usr_newpassword = $data['usr_password_new'];
                        $usersTable = $this->getUsersTable();
                        $auth = $usersTable->getUserByPasswordToken($usr_token);

                        $password = $this->encriptPassword($this->getStaticSalt(), $usr_newpassword, $auth->usr_password_salt);
                        $usersTable->savePassword($usr_token, $password);
                        $usersTable->deactivatePasswordToken($auth->usr_id);

                        return $this->redirect()->toRoute('auth/default', array('controller' => 'registration', 'action' => 'password-change-success'));
                    }
            }

    }

    public function forgottenPasswordChangeDeactivatedAction()
    {

    }

    public function passwordSendSuccessAction()
    {
        $usr_email = null;
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            foreach($flashMessenger->getMessages() as $key => $value) {
                $usr_email .=  $value;
            }
        }
        return new ViewModel(array('usr_email' => $usr_email));
    }

    public function passwordChangeSuccessAction()
    {

    }

    public function prepareData($data)
    {
        $data['usr_active'] = 0;
        $data['usr_password_salt'] = $this->generateDynamicSalt();
        $data['usr_password'] = $this->encriptPassword(
            $this->getStaticSalt(),
            $data['usr_password'],
            $data['usr_password_salt']
        );
        $data['usrl_id'] = 2;
        $data['lng_id'] = 1;
//		$data['usr_registration_date'] = date('Y-m-d H:i:s');
        $date = new \DateTime();
        $data['usr_registration_date'] = $date->format('Y-m-d H:i:s');
        $data['usr_registration_token'] = md5(uniqid(mt_rand(), true)); // $this->generateDynamicSalt();
//		$data['usr_registration_token'] = uniqid(php_uname('n'), true);	
        $data['usr_email_confirmed'] = 0;
        $data['passwordchangetoken'] = 0;
        return $data;
    }

    public function generateDynamicSalt()
    {
        $dynamicSalt = '';
        for ($i = 0; $i < 50; $i++) {
            $dynamicSalt .= chr(rand(33, 126));
        }
        return $dynamicSalt;
    }

    public function getStaticSalt()
    {
        $staticSalt = '';
        $config = $this->getServiceLocator()->get('Config');
        $staticSalt = $config['static_salt'];
        return $staticSalt;
    }

    public function encriptPassword($staticSalt, $password, $dynamicSalt)
    {
        return $password = md5($staticSalt . $password . $dynamicSalt);
    }

    public function generatePassword($l = 8, $c = 0, $n = 0, $s = 0) {
        // get count of all required minimum special chars
        $count = $c + $n + $s;
        $out = '';
        // sanitize inputs; should be self-explanatory
        if(!is_int($l) || !is_int($c) || !is_int($n) || !is_int($s)) {
            trigger_error('Argument(s) not an integer', E_USER_WARNING);
            return false;
        }
        elseif($l < 0 || $l > 20 || $c < 0 || $n < 0 || $s < 0) {
            trigger_error('Argument(s) out of range', E_USER_WARNING);
            return false;
        }
        elseif($c > $l) {
            trigger_error('Number of password capitals required exceeds password length', E_USER_WARNING);
            return false;
        }
        elseif($n > $l) {
            trigger_error('Number of password numerals exceeds password length', E_USER_WARNING);
            return false;
        }
        elseif($s > $l) {
            trigger_error('Number of password capitals exceeds password length', E_USER_WARNING);
            return false;
        }
        elseif($count > $l) {
            trigger_error('Number of password special characters exceeds specified password length', E_USER_WARNING);
            return false;
        }

        // all inputs clean, proceed to build password

        // change these strings if you want to include or exclude possible password characters
        $chars = "abcdefghijklmnopqrstuvwxyz";
        $caps = strtoupper($chars);
        $nums = "0123456789";
        $syms = "!@#$%^&*()-+?";

        // build the base password of all lower-case letters
        for($i = 0; $i < $l; $i++) {
            $out .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        // create arrays if special character(s) required
        if($count) {
            // split base password to array; create special chars array
            $tmp1 = str_split($out);
            $tmp2 = array();

            // add required special character(s) to second array
            for($i = 0; $i < $c; $i++) {
                array_push($tmp2, substr($caps, mt_rand(0, strlen($caps) - 1), 1));
            }
            for($i = 0; $i < $n; $i++) {
                array_push($tmp2, substr($nums, mt_rand(0, strlen($nums) - 1), 1));
            }
            for($i = 0; $i < $s; $i++) {
                array_push($tmp2, substr($syms, mt_rand(0, strlen($syms) - 1), 1));
            }

            // hack off a chunk of the base password array that's as big as the special chars array
            $tmp1 = array_slice($tmp1, 0, $l - $count);
            // merge special character(s) array with base password array
            $tmp1 = array_merge($tmp1, $tmp2);
            // mix the characters up
            shuffle($tmp1);
            // convert to string for output
            $out = implode('', $tmp1);
        }

        return $out;
    }

    public function getUsersTable()
    {
        if (!$this->usersTable) {
            $sm = $this->getServiceLocator();
            $this->usersTable = $sm->get('Auth\Model\UsersTable');
        }
        return $this->usersTable;
    }

    public function letterAction()
    {
    }

    public function sendConfirmationEmail($auth)
    {
        // $view = $this->getServiceLocator()->get('View');
        $transport = $this->getServiceLocator()->get('mail.transport');
        $htmlcontent = '<div class="">
<div class="">
    <div class="email-header">
        <span class="title">Atrakcje Krakowa</span>
    </div>
    <div class="email-content">
        <span class="title1">Prawie ukończono!</span></br>

        <span class="title2">Jeśli nie rejestrowałeś się na portalu Atrakcje Krakowa zignoruj tę wiadomość.</span>
    </div>
    <div class="registration-button-area">
        <a href="http://' . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $this->url()->fromRoute('auth/default', array(
                'controller' => 'registration',
                'action' => 'confirm-email',
                'id' => $auth->usr_registration_token)) . '"><div class="registration-button">Kliknij tutaj aby dokończyć rejestrację!</div></a>
    </div>
    </div>
</div>
<style>

.registration-button-area
{
    padding: 10px 10px 20px 10px;
    width: 300px;
}

.email-header
{
    border: 2px solid #1F80B0;
    text-align: center;
    padding: 20px;
    font-size: 33px;
    color: #2aabd2;
    background-color: #2a5d84;
    text-decoration: underline;
    text-decoration-color: #2aabd2;
}
.title

    {
        border-bottom: 5px solid #eb9316;
    }
.email-content
{
    border: 2px solid #1F80B0;
    padding: 20px 20px;
    margin: 20px 10px;
}

    .title1
    {
        font-size: 15px;
        font-weight: 700;
    }
    .registration-button
    {
        padding: 20px 10px;
        background-color: #2a5d84;
        border-radius: 10px;
        color: #fff;
    }

    a
    {
     text-decoration: none;
     color: #fff;
    }

.registration-button:hover
    {
        background-color: #2a6496;
    }
</style>';

        $text = new MimePart($htmlcontent);
        $text->type = 'text/html';
        $text->charset = 'UTF-8';

        $body = new MimeMessage();
        $body->setParts(array($text));


        $message = new Message();
        $this->getRequest()->getServer();  //Server vars
        $message->addTo($auth->usr_email)
            ->setEncoding('UTF-8')
            ->addFrom('tellir@op.pl')
            ->setSubject('Proszę, potwierdź rejestrację!')
            ->setBody($body);
        $transport->send($message);
    }

    public function sendPasswordTokenByEmail($usr_email, $token, $username)
    {
        $transport = $this->getServiceLocator()->get('mail.transport');
        $message = new Message();
        $htmlcontent = '<div class="">
<div class="">
    <div class="email-header">
        <span class="title">Atrakcje Krakowa ©</span>
    </div>
    <div class="email-content">
        <span class="title1">Witaj, ' . $username .  '!</span></br>

        <span class="title2">Jeśli nie prosiłeś o zmianę hasła na portalu Atrakcje Krakowa zignoruj tę wiadomość.</span>
    </div>
    <div class="registration-button-area">
        <a href="http://' . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $this->url()->fromRoute('auth/default', array(
                'controller' => 'registration',
                'action' => 'forgotten-password-change',
                'id' => $token)) . '"><div class="registration-button">Kliknij tutaj aby zmienić hasło!</div></a>
    </div>
    </div>
</div>
<style>

.registration-button-area
{
    padding: 10px 10px 20px 10px;
    width: 300px;
}

.email-header
{
    border: 2px solid #1F80B0;
    text-align: center;
    padding: 20px;
    font-size: 33px;
    color: #2aabd2;
    background-color: #2a5d84;
    text-decoration: underline;
    text-decoration-color: #2aabd2;
}
.title

    {
        border-bottom: 5px solid #eb9316;
    }
.email-content
{
    border: 2px solid #1F80B0;
    padding: 20px 20px;
    margin: 20px 10px;
}

    .title1
    {
        font-size: 15px;
        font-weight: 700;
    }
    .registration-button
    {
        padding: 20px 10px;
        background-color: #2a5d84;
        border-radius: 10px;
        color: #fff;
    }

    a
    {
     text-decoration: none;
     color: #fff;
    }

.registration-button:hover
    {
        background-color: #2a6496;
    }
</style>';

        $text = new MimePart($htmlcontent);
        $text->type = 'text/html';
        $text->charset = 'UTF-8';

        $body = new MimeMessage();
        $body->setParts(array($text));

        $this->getRequest()->getServer();  //Server vars
        $message->addTo($usr_email)
            ->setEncoding('UTF-8')
            ->addFrom('praktiki@coolcsn.com')
            ->setSubject('Zmiana hasła na Atrakcje Krakowa ©')
            ->setBody($body)
        ;
        $transport->send($message);
    }
}
<?php
namespace Auth\Form;

use  Zend\Form\Form;
use ReCaptcha2\Captcha\ReCaptcha2;
use Zend\Form\Element;


class RegistrationForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('registration');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'id' => 'usr_name',
                'name' => 'usr_name',
                'class' => 'form-control',
                'placeholder' => 'Użytkownik',

            ),
        ));
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'id' => 'usr_firstname',
                'type'  => 'text',
                'class' => 'form-control',
                'placeholder' => 'Imię',
            ),
        ));
        $this->add(array(
            'name' => 'lastname',
            'attributes' => array(
                'id' => 'usr_lastname',
                'type'  => 'text',
                'class' => 'form-control',
                'placeholder' => 'Nazwisko',

            ),
        ));
        $this->add(array(
            'name' => 'usr_email',
            'attributes' => array(
                'id' => 'usr_email',
                'name' => 'usr_email',
                'type'  => 'email',
                'class' => 'form-control',
                'placeholder' => 'Adres e-mail'
            ),
        ));
		
        $this->add(array(
            'name' => 'usr_password',
            'attributes' => array(
                'id' => 'usr_password',
                'name' => 'usr_password',
                'type'  => 'password',
                'class' => 'form-control',
                'placeholder' => 'Hasło',
            ),
        ));
		
        $this->add(array(
            'name' => 'usr_password_confirm',
            'attributes' => array(
                'id' => 'usr_password_confirm',
                'name' => 'usr_password_confirm',
                'type'  => 'password',
                'class' => 'form-control',
                'placeholder' => 'Powtórz hasło'
            ),
        ));

        $this->add(array(
            'name' => 'regulations_checkbox',
            'attributes' => array(
                'id' => 'regulations_checkbox',
                'name' => 'regulations_checkbox',
                'type'  => 'Checkbox',
            ),
        ));

		$this->add(array(
            'name' => 'captcha',
            'type' => Element\Captcha::class,
            'attributes' => array(
                'id' => 'captcha',
                'name' => 'captcha',
                'data-callback' => 'recaptchaCallback',
            ),
			'options' => array(
			    'captcha' => array(
                    'class' =>  ReCaptcha2::class,
                    'options' => array (
                        'secretKey' => '6LccTToUAAAAANW3RuTGimiOQnBvXSMRal4YPG12',
                        'siteKey' => '6LccTToUAAAAADUCg6CmfLCq7yKKQmVEGOBUi6g-',
                    ),
                ),
			),
		));
		
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
                'class' => 'btn btn-block btn-info'
            ),
        )); 
    }
}
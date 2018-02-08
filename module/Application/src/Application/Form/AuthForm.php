<?php
namespace Application\Form;

use Zend\Form\Form;

class AuthForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('application');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => 'Użytkownik',
                'class' => 'form-control',
            ),
        ));
        $this->add(array(
            'name' => 'usr_password',
            'attributes' => array(
                'type'  => 'password',
                'placeholder' => 'Hasło',
                'class' => 'form-control',
            ),
        ));
        $this->add(array(
            'name' => 'rememberme',
			'type' => 'checkbox', // 'Zend\Form\Element\Checkbox',			
//            'attributes' => array( // Is not working this way
//                'type'  => '\Zend\Form\Element\Checkbox',
//            ),
        ));			
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Zaloguj',
                'id' => 'submitbutton',
                'class' => 'btn btn-block btn-info'
            ),
        )); 
    }
}
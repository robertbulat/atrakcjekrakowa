<?php
namespace Auth\Form;

use Zend\Form\Form;

class ForgottenPasswordForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('password_change');
        $this->setAttribute('method', 'post');
		
        $this->add(array(
            'name' => 'usr_email',
            'attributes' => array(
                'type'  => 'email',
                'class' => 'form-control',
                'id' => 'usr_email'
            ),
        ));	
		
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Wyślij',
                'id' => 'submitbutton',
                'class' => 'btn btn-block btn-info'
            ),
        )); 
    }
}
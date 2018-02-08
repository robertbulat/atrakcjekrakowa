<?php
namespace Application\Form;

use Zend\Form\Form;

class ForgottenPasswordChangeForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('forgotten-password-change');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'usr_password_new',
            'attributes' => array(
                'id' => 'usr_password_new',
                'type'  => 'password',
                'class' => 'form-control',
                'placeholder' => 'Nowe hasło',
            ),
        ));

        $this->add(array(
            'name' => 'usr_password_new_confirm',
            'attributes' => array(
                'id' => 'usr_password_new_confirm',
                'type'  => 'password',
                'class' => 'form-control',
                'placeholder' => 'Powtórz hasło'
            ),
        ));

        $this->add(array(
            'name' => 'usr_token',
            'attributes' => array(
                'id' => 'usr_token',
                'type'  => 'hidden'
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
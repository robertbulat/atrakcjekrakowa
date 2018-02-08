<?php
namespace Application\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ForgottenPasswordChangeFilter extends InputFilter
{
    public function __construct($sm)
    {
        $this->add(array(
            'name'     => 'usr_password_new',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 8,
                        'max'      => 30,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name'     => 'usr_password_new_confirm',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 8,
                        'max'      => 30,
                    ),
                ),
                array(
                    'name'    => 'regex',
                    'options' => array(
                        'pattern' => '/^[A-Za-z0-9_()]/',
                    ),
                ),
                array(
                    'name'    => 'Identical',
                    'options' => array(
                        'token' => 'usr_password_new'
                    ),
                ),
            ),
        ));
    }
}
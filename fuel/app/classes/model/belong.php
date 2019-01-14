<?php

class Model_Belong extends Orm\Model
{

    protected static $_table_name = 'belong'; 
    protected static $_primary_key = array('id_user' , 'id_group');
    protected static $_properties = array(
        'id_user' => array(
            'data_type' => 'int'),
        'id_group' => array(
            'data_type' => 'int'),
    );

}
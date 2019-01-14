<?php

class Model_Message extends Orm\Model
{

    protected static $_table_name = 'messages'; 
    
    protected static $_properties = array(
    	'id' => array(
            'data_type' => 'int'),
        'description' => array(
            'data_type' => 'varchar'),
        'id_chat' => array(
            'data_type' => 'int'),
        'date' => array(
            'data_type' => 'varchar'),
        'id_user' => array(
            'data_type' => 'int'),
    );
    protected static $_primary_key = array('id');

}
<?php

class Model_Chat extends Orm\Model
{

    protected static $_table_name = 'chats'; 
    
    protected static $_properties = array(
    	'id' => array(
            'data_type' => 'int'),
        'id_user1' => array(
            'data_type' => 'int'),
        'id_user2' => array(
            'data_type' => 'int'),
    );
    protected static $_primary_key = array('id');

}
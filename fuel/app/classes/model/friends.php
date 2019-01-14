<?php

class Model_Friends extends Orm\Model
{

    protected static $_table_name = 'friend'; 
    protected static $_properties = array(
        'id_user_receive' => array(
            'data_type' => 'int'
        ),
        'id_user_send' => array(
            'data_type' => 'int'
        ),
        'state' => array(
            'data_type' => 'int'
        )
    );

	protected static $_primary_key = array('id_user_receive' , 'id_user_send');

}
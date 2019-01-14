<?php

class Model_Types extends Orm\Model
{

    protected static $_table_name = 'types'; 
    protected static $_properties = array('id',
        'name' => array(
            'data_type' => 'varchar'
        ), 

    );
    protected static $_has_many = array(
        'events' => array(
            'key_from' => 'id',
            'model_to' => 'Model_Events',
            'key_to' => 'id_type',
            'cascade_save' => true,
            'cascade_delete' => false,
        )
    );

}
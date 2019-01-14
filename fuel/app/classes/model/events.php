<?php

class Model_Events extends Orm\Model
{

    protected static $_table_name = 'events'; 
    protected static $_properties = array('id',
        'title' => array(
            'data_type' => 'int'
        ), 
        'description' => array(
            'data_type' => 'String'
        ),
        'image' => array(
            'data_type' => 'String'
        ),
        'lat' => array(
            'data_type' => 'float'
        ),
        'lon' => array(
            'data_type' => 'float'
        ),
        'date' => array(
            'data_type' => 'varchar'
        ),
        'url' => array(
            'data_type' => 'varchar'
        ),
        'id_user' => array(
            'data_type' => 'int'
        ),
        'id_type' => array(
            'data_type' => 'int'
        )
    );

    protected static $_belongs_to = array(
        'users' => array(
            'key_from' => 'id_user',
            'model_to' => 'Model_Users',
            'key_to' => 'id',
            'cascade_save' => true,
            // cuando borro evento borro usuario
            'cascade_delete' => false,
        ),
        'types' => array(
            'key_from' => 'id_type',
            'model_to' => 'Model_Types',
            'key_to' => 'id',
            'cascade_save' => true,
            // cuando borro evento borro tipo
            'cascade_delete' => false,
        )
    );
    protected static $_has_many = array(
        'comments' => array(
            'key_from' => 'id',
            'model_to' => 'Model_Comments',
            'key_to' => 'id_event',
            'cascade_save' => true,
            'cascade_delete' => false,
        )
    );

}
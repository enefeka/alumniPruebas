<?php

class Model_Comments extends Orm\Model
{

    protected static $_table_name = 'comments'; 
    protected static $_properties = array('id',
        'title' => array(
            'data_type' => 'int'
        ), 
        'description' => array(
            'data_type' => 'String'
        ),
        'date' => array(
            'data_type' => 'String'
        ),
        'id_event' => array(
            'data_type' => 'int'
        ),
        'id_user' => array(
            'data_type' => 'float'
        ),
    );

    protected static $_belongs_to = array(
    'events' => array(
        'key_from' => 'id_event',
        'model_to' => 'Model_Events',
        'key_to' => 'id',
        'cascade_save' => true,
        // borrar evento cuando se borra comentario
        'cascade_delete' => false,
        ),
    'users' => array(
        'key_from' => 'id',
        'model_to' => 'Model_Users',
        'key_to' => 'id_rol',
        'cascade_save' => true,
        // borrar usuario cuando se borra comentario
        'cascade_delete' => false,
    )
    );

}
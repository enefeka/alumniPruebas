<?php

class Model_Roles extends Orm\Model
{

    protected static $_table_name = 'roles'; 
    protected static $_properties = array('id','type');

    protected static $_has_many = array(
        'users' => array(
            'key_from' => 'id',
            'model_to' => 'Model_Users',
            'key_to' => 'id_rol',
            'cascade_save' => true,
            'cascade_delete' => false,
    )
);

}
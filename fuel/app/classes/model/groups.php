<?php

class Model_Groups extends Orm\Model
{

    protected static $_table_name = 'groups'; 
    protected static $_properties = array('id',
        'name' => array(
            'data_type' => 'varchar'
        )
    );
}
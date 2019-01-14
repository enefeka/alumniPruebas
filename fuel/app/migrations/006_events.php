<?php
namespace Fuel\Migrations;

class Events
{

    function up()
    {
        \DBUtil::create_table('events', array(
            'id' => array('type' => 'int', 'constraint' => 5, 'auto_increment' => true),
            'title' => array('type' => 'varchar', 'constraint' => 250),
            'description' => array('type' => 'varchar', 'constraint' => 2500),
            'image' => array('type' => 'varchar', 'constraint' => 250, 'null' => true),
            'lat' => array('type' => 'varchar', 'constraint' => 100, 'null' => true),
            'lon' => array('type' => 'varchar', 'constraint' => 100, 'null' => true),
            'date' => array('type' => 'varchar', 'constraint' => 100, 'null' => true),
            'url' => array('type' => 'varchar', 'constraint' => 150, 'null' => true),
            'id_user' => array('type' => 'int', 'constraint' => 5,'null'=>true),
            'id_type' => array('type' => 'int', 'constraint' => 5),
        ), array('id'),
            true,
            'InnoDB',
            'utf8_unicode_ci',
            array(
                array(
                    'constraint' => 'claveAjenaEventsAUsers',
                    'key' => 'id_user',
                    'reference' => array(
                        'table' => 'users',
                        'column' => 'id',
                    ),
                    // cuando borre un usuario no borran sus eventos creados id_user = null
                    'on_update' => 'CASCADE',
                    'on_delete' => 'SET NULL'
                ),
                array(
                    'constraint' => 'claveAjenaEventsATypes',
                    'key' => 'id_type',
                    'reference' => array(
                        'table' => 'types',
                        'column' => 'id',
                    ),
                    //no puedo borrar un tipo si tiene eventos asignados
                    'on_update' => 'CASCADE',
                    'on_delete' => 'RESTRICT'
                ),
            ));
    }

    function down()
    {
       \DBUtil::drop_table('events');
    }
}
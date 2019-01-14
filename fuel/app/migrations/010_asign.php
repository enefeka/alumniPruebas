<?php
namespace Fuel\Migrations;

class Asign
{

    function up()
    {
        \DBUtil::create_table('asign', array(
            'id_event' => array('type' => 'int', 'constraint' => 5),
            'id_group' => array('type' => 'int', 'constraint' => 5),
        ), array('id_event','id_group'),
            true,
            'InnoDB',
            'utf8_unicode_ci',
            array(
                array(
                    'constraint' => 'claveAjenaasignAevents',
                    'key' => 'id_event',
                    'reference' => array(
                        'table' => 'events',
                        'column' => 'id',
                    ),
                    // cuando borro un evento lo desasigno del grupo
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                ),
                array(
                    'constraint' => 'claveAjenaasignAGroups',
                    'key' => 'id_group',
                    'reference' => array(
                        'table' => 'groups',
                        'column' => 'id',
                    ),
                    // cuando borro un grupo le desasigno los eventos
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                )
            ));


    }

    function down()
    {
       \DBUtil::drop_table('asign');
    }
}
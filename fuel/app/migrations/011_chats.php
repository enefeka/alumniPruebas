<?php
namespace Fuel\Migrations;

class Chats
{

    function up()
    {
        \DBUtil::create_table('chats', array(
        	'id' => array('type' => 'int', 'constraint' => 5, 'auto_increment' => true),
            'id_user1' => array('type' => 'int', 'constraint' => 5),
            'id_user2' => array('type' => 'int', 'constraint' => 5),
        ), array('id'),
            true,
            'InnoDB',
            'utf8_unicode_ci',
            array(
                array(
                    'constraint' => 'claveAjenachatsAevents',
                    'key' => 'id_user1',
                    'reference' => array(
                        'table' => 'users',
                        'column' => 'id',
                    ),
                    // cuando borro un usuario se borran sus chatss
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                ),
                array(
                    'constraint' => 'claveAjenachatsAGroups',
                    'key' => 'id_user2',
                    'reference' => array(
                        'table' => 'users',
                        'column' => 'id',
                    ),
                    // cuando borro un usuario se borran sus chatss
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                )
            ));


    }

    function down()
    {
       \DBUtil::drop_table('chats');
    }
}
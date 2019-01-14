<?php
namespace Fuel\Migrations;

class Messages
{

    function up()
    {
        \DBUtil::create_table('messages', array(
        	'id' => array('type' => 'int', 'constraint' => 5, 'auto_increment' => true),
            'description' => array('type' => 'varchar', 'constraint' => 200),
            'date' => array('type' => 'varchar', 'constraint' => 100, 'null' => true),
            'id_chat' => array('type' => 'int', 'constraint' => 5),
            'id_user' => array('type' => 'int', 'constraint' => 5),

        ), array('id'),
            true,
            'InnoDB',
            'utf8_unicode_ci',
            array(
                array(
                    'constraint' => 'claveAjenamessagesAchat',
                    'key' => 'id_chat',
                    'reference' => array(
                        'table' => 'chats',
                        'column' => 'id',
                    ),
                    // cuando borro un chat se borran sus messagess
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                ),
                array(
                    'constraint' => 'claveAjenamessagesAUsers',
                    'key' => 'id_user',
                    'reference' => array(
                        'table' => 'users',
                        'column' => 'id',
                    ),
                    // cuando borro un usuario se borran sus messagess
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                )
            ));


    }

    function down()
    {
       \DBUtil::drop_table('messages');
    }
}
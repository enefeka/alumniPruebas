<?php
namespace Fuel\Migrations;

class Friend
{

    function up()
    {
        \DBUtil::create_table('friend', array(
            'id_user_send' => array('type' => 'int', 'constraint' => 5,),
            'id_user_receive' => array('type' => 'int', 'constraint' => 5),
            'state' => array('type' => 'int', 'constraint' => 2)

        ), array('id_user_send', 'id_user_receive'),
            true,
            'InnoDB',
            'utf8_unicode_ci',
            array(
                array(
                    'constraint' => 'claveAjenaFriendSendAUsers',
                    'key' => 'id_user_send',
                    'reference' => array(
                        'table' => 'users',
                        'column' => 'id',
                    ),
                    // cuando borro un usuario borro amistad
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                ),
                array(
                    'constraint' => 'claveAjenaFriendReceiveAUsers',
                    'key' => 'id_user_receive',
                    'reference' => array(
                        'table' => 'users',
                        'column' => 'id',
                    ),
                    // cuando borro un usuario borro amistad
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                )
            ));
    }

    function down()
    {
       \DBUtil::drop_table('friend');
    }
}
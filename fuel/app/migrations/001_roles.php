<?php
namespace Fuel\Migrations;

class Roles
{

    function up()
    {
        \DBUtil::create_table('roles', array(
            'id' => array('type' => 'int', 'constraint' => 1),
            'type' => array('type' => 'varchar', 'constraint' => 100),

        ), array('id'));

        \DB::query("ALTER TABLE `roles` ADD UNIQUE (`type`)")->execute();
        
        \DB::query("INSERT INTO `roles` (`id`, `type`) VALUES ('1', 'admin');")->execute();
        \DB::query("INSERT INTO `roles` (`id`, `type`) VALUES ('2', 'profesor');")->execute();
        \DB::query("INSERT INTO `roles` (`id`, `type`) VALUES ('3', 'alumno');")->execute();

    }

    function down()
    {
       \DBUtil::drop_table('roles');
    }
}
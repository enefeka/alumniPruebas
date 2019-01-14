<?php
namespace Fuel\Migrations;

class Groups
{

    function up()
    {
        \DBUtil::create_table('groups', array(
            'id' => array('type' => 'int', 'constraint' => 5, 'auto_increment' => true),
            'name' => array('type' => 'varchar', 'constraint' => 100),
        ), array('id'),
            true,
            'InnoDB',
            'utf8_unicode_ci',
            null);

        //\DB::query("INSERT INTO `groups` (`id`, `name`) VALUES (NULL, 'all');")->execute();
        \DB::query("INSERT INTO `groups` (`id`, `name`) VALUES (NULL, 'Profesores');")->execute();
        \DB::query("INSERT INTO `groups` (`id`, `name`) VALUES (NULL, 'Alumnos');")->execute();
        \DB::query("INSERT INTO `groups` (`id`, `name`) VALUES (NULL, 'Ex-alumnos');")->execute();
        
    }

    function down()
    {
       \DBUtil::drop_table('groups');
    }
}
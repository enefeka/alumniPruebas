<?php
namespace Fuel\Migrations;

class Types
{

    function up()
    {
        \DBUtil::create_table('types', array(
            'id' => array('type' => 'int', 'constraint' => 5, 'auto_increment' => true),
            'name' => array('type' => 'varchar', 'constraint'=>50),

        ), array('id'));
        \DB::query("INSERT INTO `types` (`id`, `name`) VALUES (NULL, 'Eventos');")->execute();
        \DB::query("INSERT INTO `types` (`id`, `name`) VALUES (NULL, 'Ofertas trabajo');")->execute();
        \DB::query("INSERT INTO `types` (`id`, `name`) VALUES (NULL, 'Notificaciones');")->execute();
        \DB::query("INSERT INTO `types` (`id`, `name`) VALUES (NULL, 'Noticias');")->execute();

    }

    function down()
    {
       \DBUtil::drop_table('types');
    }
}
<?php
namespace Fuel\Migrations;

class Belong
{

    function up()
    {
        \DBUtil::create_table('belong', array(
            'id_user' => array('type' => 'int', 'constraint' => 5),
            'id_group' => array('type' => 'int', 'constraint' => 5),
        ), array('id_user','id_group'),
            true,
            'InnoDB',
            'utf8_unicode_ci',
            array(
                array(
                    'constraint' => 'claveAjenabelongAUsers',
                    'key' => 'id_user',
                    'reference' => array(
                        'table' => 'users',
                        'column' => 'id',
                    ),
                    //cuando borro un -usuario- deja de -pertenecer- al grupo
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                ),
                array(
                    'constraint' => 'claveAjenabelongAGroup',
                    'key' => 'id_group',
                    'reference' => array(
                        'table' => 'groups',
                        'column' => 'id',
                    ),
                    // cuando borro un -grupo- los usuarios dejan de pertenecer al grupo
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                )
            ));

        //Admin pertenece a todos los grupos al crearse
        \DB::query("INSERT INTO `belong` 
            (`id_user`, `id_group`) VALUES (1, 1);")->execute();
        \DB::query("INSERT INTO `belong` 
            (`id_user`, `id_group`) VALUES (1, 2);")->execute();
        \DB::query("INSERT INTO `belong` 
            (`id_user`, `id_group`) VALUES (1, 3);")->execute();
        
    }

    function down()
    {
       \DBUtil::drop_table('belong');
    }
}
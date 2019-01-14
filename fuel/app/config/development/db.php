<?php
/**
 * The development database settings. These get merged with the global settings.
 */

// LOCAL


return array(
	'default' => array(
		'connection'  => array(
			'dsn'        => 'mysql:host=localhost:8889;dbname=alumniPruebas',
			'username'   => 'root',
			'password'   => 'root',
		),
	),
);




// PRODUCCION

// return array(
// 	'default' => array(
// 		'connection'  => array(
// 			'dsn'        => 'mysql:host=localhost;dbname=solfamidas',
// 			'username'   => 'root',
// 			'password'   => 'root',
// 		),
// 	),
// );

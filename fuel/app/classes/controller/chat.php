<?php

use Firebase\JWT\JWT;

class Controller_Chat extends Controller_Rest
{
	private $key = 'my_secret_key';
	function post_create()
    {
        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }
        $jwt = apache_request_headers()['Authorization'];
        // valdiar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            //return $this->createResponse(400, 'Error de autentificacion');
        }
        $user = $this->decodeToken();

        // falta parametro 
        if (empty($_POST['id_user'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id_user) ');
        }

        $id_user = $_POST['id_user'];
        try 
        {
        	$userBD = Model_Users::find($id_user);
        	if ($userBD==null) {
        		return $this->createResponse(400, "No existe el usuario para crear un chat");
        	}
        	$chat = Model_Chat::find('first', array(
                        'where' => array(
                            array('id_user1', $user->data->id),
                            array('id_user2', $id_user),
                            'or' => array(
                                array('id_user2', $id_user),
                                array('id_user1', $user->data->id))
                            ),
            ));
            if ($chat != null) {
            	return $this->createResponse(400, "Ya existe un chat con ese usuario");
            }
            $props = array('id_user1' => $user->data->id,'id_user2' => $id_user);

            $newChat = new Model_Chat($props);
            $newChat->save();
            return $this->createResponse(200, "Chat creado con exito", array('chat' => $newChat));

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function post_sendMessage()
    {
        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }
        $jwt = apache_request_headers()['Authorization'];
        // valdiar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            //return $this->createResponse(400, 'Error de autentificacion');
        }
        $user = $this->decodeToken();

        // falta parametro 
        if (empty($_POST['id_chat']) || empty($_POST['description'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id_chat, description) ');
        }

        $id_chat = $_POST['id_chat'];
        $description = $_POST['description'];
        try 
        {
        	$chatDB = Model_Chat::find($id_chat);
        	if ($chatDB == null) {
        		return $this->createResponse(400, "No existe el chat");
        	}
        	if ($chatDB->id_user1 != $user->data->id && $chatDB->id_user2 != $user->data->id) {
        		return $this->createResponse(400, "El chat no pertenece al usuario");
        	}
        	date_default_timezone_set('CET');
        	$props = array('description' => $description,'id_chat' => $id_chat, 'id_user' => $user->data->id, 'date' => date('Y-m-d H:i:s'));
            $message = new Model_Message($props);
            $message->save();
            return $this->createResponse(200, "Mensaje enviado con exito");

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_messages()
    {
        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }
        $jwt = apache_request_headers()['Authorization'];
        // valdiar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            //return $this->createResponse(400, 'Error de autentificacion');
        }
        $user = $this->decodeToken();

        // falta parametro 
        if (empty($_GET['id_chat'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id_chat) ');
        }

        $id_chat = $_GET['id_chat'];
        try 
        {
        	$chatDB = Model_Chat::find($id_chat);
        	if ($chatDB == null) {
        		return $this->createResponse(400, "No existe el chat");
        	}
        	if ($chatDB->id_user1 != $user->data->id && $chatDB->id_user2 != $user->data->id) {
        		return $this->createResponse(400, "El chat no pertenece al usuario");
        	}
        	
        	$messages = Model_Message::find('all', array(
					    'where' => array(
					        array('id_chat', $id_chat),
					    ),
					    'order_by' => array('id' => 'asc'),
			));

			if ($messages == null) {
				return $this->createResponse(400, "Aun no tienes mensajes en este chat");
			}

            return $this->createResponse(200, "Listado mensajes",array('messages' => Arr::reindex($messages)));

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_chats()
    {
        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }
        $jwt = apache_request_headers()['Authorization'];
        // valdiar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            //return $this->createResponse(400, 'Error de autentificacion');
        }
        $user = $this->decodeToken();

        $id_user = $user->data->id;

        try 
        {
            $chats = Model_Chat::find('all', array(
                'where' => array(
                    array('id_user1', $id_user),
                    'or' => array(
                    array('id_user2', $id_user),
                )
                ),
            )); 

            if(count($chats) == 0){
                return $this->createResponse(400, "Aun no tienes chats");
            }

            foreach($chats as $keyChat => $chat) {

                $message= Model_Message::find('last', array(
                    'where' => array(
                        array('id_chat', $chat->id)
                        )
                ));

                $chat['message'] = $message;

                if($chat->id_user1 == $id_user){
                    $idUserChat = $chat->id_user2;
                }else{
                    $idUserChat = $chat->id_user1;
                }

                $userChat = Model_Users::find($idUserChat);

                $chat['user'] = $userChat;

            }

            return $this->createResponse(200, "Chats devueltos", array('chats' => Arr::reindex($chats)));

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_userstochat(){
        try{
            // falta token
            if (!isset(apache_request_headers()['Authorization']))
            {
                return $this->createResponse(400, 'Token no encontrado');
            }
            $jwt = apache_request_headers()['Authorization'];
            // valdiar token
            try {

                $this->validateToken($jwt);
            } catch (Exception $e) {

                //return $this->createResponse(400, 'Error de autentificacion');
            }
            $user = $this->decodeToken();

            $id_user = $user->data->id;

            $friendUsers = \DB::query('SELECT * FROM users
                                        JOIN friend ON friend.id_user_send = '.$id_user.'
                                        AND users.id = friend.id_user_receive
                                        WHERE friend.state = 2
                                        UNION 
                                        SELECT * FROM users
                                        JOIN friend ON friend.id_user_receive = '.$id_user.'
                                        AND users.id = friend.id_user_send
                                        WHERE friend.state = 2
                                        ')->as_assoc()->execute();

            foreach($friendUsers as $key => $user) {   

                $chat1 = Model_Chat::find('first', array(
                    'where' => array(
                        array('id_user1', $user["id"]),
                        array('id_user2', $id_user)  
                )));

                $chat2 = Model_Chat::find('first', array(
                    'where' => array(
                        array('id_user2', $user["id"]),
                        array('id_user1', $id_user)  
                )));


                if($chat1 == null && $chat2 == null){
                    $usersToChat[] = $user;
                }

            }

            if(!isset($usersToChat)){
                return $this->createResponse(200, "No hay usuarios amigos con los que crear chat");
            }

            return $this->createResponse(200, "Usuarios amigos con los que abrir chat devueltos", array('users' => $usersToChat));

        }catch(Exception $e){
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function createResponse($code, $message, $data = [])
    {

        $json = $this->response(array(
          'code' => $code,
          'message' => $message,
          'data' => $data
          ));

        return $json;
    }	
    function validateToken($jwt)
    {
        $token = JWT::decode($jwt, $this->key, array('HS256'));

        $email = $token->data->email;
        $password = $token->data->password;
        $id = $token->data->id;

        $userDB = Model_Users::find('all', array(
            'where' => array(
                array('email', $email),
                array('password', $password),
                array('id',$id)
                )
            ));
        if($userDB != null){
            return true;
        }else{
            return false;
        }
    }
    function decodeToken()
    {

        $jwt = apache_request_headers()['Authorization'];
        $token = JWT::decode($jwt, $this->key , array('HS256'));
        return $token;
    }
}
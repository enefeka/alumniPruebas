<?php
//require_once '../../../vendor/autoload.php';
use Firebase\JWT\JWT;

class Controller_Comments extends Controller_Rest
{
    private $key = 'my_secret_key';
    protected $format = 'json';



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

            return $this->createResponse(400, 'Error de autentificacion');
        }
        $user = $this->decodeToken();

        // falta parametro 
        if (empty($_POST['title']) || empty($_POST['description'])|| empty($_POST['id_event']) ) 
            {

              return $this->createResponse(400, 'Falta parámetros obligatorios (title, description, id_event) ');
            }

        $title = $_POST['title'];
        $description = $_POST['description'];
        $id_event = $_POST['id_event'];
        try {
        	if (Model_Events::find($id_event) == null) {
        		return $this->createResponse(400, 'El evento no existe');
        	}
            $commentDB = new Model_Comments();
            $commentDB->title = $title;
            $commentDB->description = $description;
			$commentDB->id_event = $id_event;
            // horario español
            date_default_timezone_set('CET');
			$commentDB->date = date('Y-m-d H:i:s');
            $commentDB->id_user = $user->data->id;

            $commentDB->save();
            return $this->createResponse(200, 'Comentario añadido');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }
    function post_delete()
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

            return $this->createResponse(400, 'Error de autentificacion');
        }
        $user = $this->decodeToken();

        // falta parametro 
        if (empty($_POST['id_comment'])) 
            {

              return $this->createResponse(400, 'Falta parámetros obligatorios (id_comment) ');
            }

        $id_comment = $_POST['id_comment'];
        try {
        	$commentDB = Model_Comments::find($id_comment);
        	if ($commentDB == null) {
        		return $this->createResponse(400, 'El Comentario no existe');
        	}
        	$eventDB = Model_Events::find($commentDB->id_event);
        	// comentario propio, evento propio, user admin
        	if ($commentDB->id_user == $user->data->id || $user->data->id == $eventDB->id_user || $user->data->id_rol == 1) {
        		$commentDB->delete();
        		return $this->createResponse(200, 'Comentario borrado');
        	}

            return $this->createResponse(401, 'No autorizado');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function decodeToken()
    {

        $jwt = apache_request_headers()['Authorization'];
        $token = JWT::decode($jwt, $this->key , array('HS256'));
        return $token;
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

    function createResponse($code, $message, $data = [])
    {

        $json = $this->response(array(
          'code' => $code,
          'message' => $message,
          'data' => $data
          ));

        return $json;
    }

}
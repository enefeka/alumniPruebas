<?php
//require_once '../../../vendor/autoload.php';
use Firebase\JWT\JWT;

class Controller_Events extends Controller_Rest
{
    private $key = 'my_secret_key';
    protected $format = 'json';
    private $urlPro = 'http://h2744356.stratoserver.net/solfamidas/alumniCEV/public/assets/img/';
    private $urlDev = 'http://localhost:8888/alumniCEV/public/assets/img/';

    function post_create()
    {
        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }
        $jwt = apache_request_headers()['Authorization'];
        // valdiar token
        try 
        {
            $this->validateToken($jwt);
        } 
        catch (Exception $e) 
        {

            return $this->createResponse(400, 'Error de autentificacion');
        }
        // validar rol de admin
        $user = $this->decodeToken();


        if (empty($_POST['title']) || empty($_POST['description']) || empty($_POST['id_group'])|| empty($_POST['id_type'])) 
        {

          return $this->createResponse(400, 'Falta parámetros obligatorios (title, description,[id_group]), id_type ');
        }

        $title = $_POST['title'];
        $description = $_POST['description'];
        $array_id_group = $_POST['id_group'];
        $id_type = $_POST['id_type'];

        try {
            $eventDB = new Model_Events();
            $eventDB->title = $title;
            $eventDB->description = $description;

            $typeDB = Model_Types::find($id_type);
            if (empty($typeDB)) {
                return $this->createResponse(400, 'No existe el tipo de evento mandado por parametro');
            }

            $eventDB->id_type = $id_type;

            if (!empty($_POST['lat'])) {
            	$eventDB->lat = $_POST['lat'];
            }
            if (!empty($_POST['lon'])) {
            	$eventDB->lon = $_POST['lon'];
            }
            if (!empty($_POST['url'])) {
                $eventDB->url = $_POST['url'];
            }
            // horario español
            date_default_timezone_set('CET');
            $eventDB->date = date('Y-m-d H:i:s');

            //return $this->createResponse(500, 'files', $_FILES);
            if (!empty($_FILES['image'])) {
                // foto
                // Custom configuration for this upload
                $config = array(
                    'path' => DOCROOT . 'assets/img',
                    'randomize' => true,
                    'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png'),
                );
                // process the uploaded files in $_FILES
                Upload::process($config);
                // if there are any valid files
                if (Upload::is_valid())
                {
                    // save them according to the config
                    Upload::save();
                    foreach(Upload::get_files() as $file)
                    {
                        //$eventDB->image = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/alumniCEV/public/assets/img/'.$file['saved_as'];
                        $eventDB->image = $this->urlPro.$file['saved_as'];
                    }
                }
                // and process any errors
                foreach (Upload::get_errors() as $file)
                {
                    return $this->createResponse(500, 'Error al subir la imagen', $file);
                }
            }
            

            $eventDB->id_user = $user->data->id;
            $eventDB->save();
            foreach ($array_id_group as $key => $idGroup) 
            {

                $props = array('id_event'=>$eventDB->id, 'id_group' => $idGroup);
                $asignDB = new Model_Asign($props);
                $asignDB->save();
            }
            return $this->createResponse(200, 'Evento creado');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
            try {
                $eventDB->delete();
            } catch (Exception $e) {
                return $this->createResponse(500, $e->getMessage());
            }
        }
    }

    function post_update()
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
        // falta parametro 
        if (empty($_POST['id']) ) {
            return $this->createResponse(400, 'Falta parametro id');
        }
        $id = $_POST['id'];
        //admin modifica todos y el usuario el suyo propio
        $user = $this->decodeToken();
        /*
        if ($user->data->id_rol != 1 && $user->data->id != $id) {
            return $this->createResponse(401, 'No autorizado');
        }
        */
        
        try {
            
            $eventDB = Model_Events::find($id);
            if ($eventDB == null) {
                return $this->createResponse(400, 'No existe el evento');
            }

            if (!empty($_POST['title']) ) {
                $eventDB->title = $_POST['title'];
            }
            if (!empty($_POST['description']) ) {
                $eventDB->description = $_POST['description'];
            }
            if (!empty($_POST['lat']) ) {
                $eventDB->lat = $_POST['lat'];
            }
            if (!empty($_POST['lon']) ) {
                $eventDB->lon = $_POST['lon'];
            }
            if (!empty($_POST['url']) ) {
                $eventDB->url = $_POST['url'];
            }
            if (!empty($_FILES['image'])) {
                // foto
                // Custom configuration for this upload
                $config = array(
                    'path' => DOCROOT . 'assets/img',
                    'randomize' => true,
                    'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png'),
                );
                // process the uploaded files in $_FILES
                Upload::process($config);
                // if there are any valid files
                if (Upload::is_valid())
                {
                    // save them according to the config
                    Upload::save();
                    foreach(Upload::get_files() as $file)
                    {
                        //$eventDB->image = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/alumniCEV/public/assets/img/'.$file['saved_as'];
                        $eventDB->image = $this->urlPro.$file['saved_as'];
                    }
                }
                // and process any errors
                foreach (Upload::get_errors() as $file)
                {
                    return $this->createResponse(500, 'Error al subir la imagen', $file);
                }
            }

            $eventDB->save();
            return $this->createResponse(200, 'Evento actualizado');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_events()
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
        $id= $user->data->id;
        if (!isset($_GET['type']) )
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (type, 0 -> todos, 1-> eventos, 2-> ofertas trabajo, 3 -> notificaciones, 4 -> noticias) ');
        }

        $type = $_GET['type'];

        try {
            
            if ($type == 0 ) 
            {
                $query = \DB::query('SELECT DISTINCT events.*,types.name FROM belong
                    JOIN users ON belong.id_user = users.id
                    JOIN groups ON groups.id = belong.id_group
                    JOIN asign ON asign.id_group = groups.id
                    JOIN events ON events.id = asign.id_event
                    JOIN types ON types.id = events.id_type
                    WHERE users.id = '.$id.'.
                    ORDER BY events.id DESC')->as_assoc()->execute();
            }
            else
            {
                $typeDB = Model_Types::find($type);
                if ($typeDB == null) {
                    return $this->createResponse(400, 'Parametro type no valido');
                }
                $query = \DB::query('SELECT DISTINCT events.*,types.name FROM belong
                                        JOIN users ON belong.id_user = users.id
                                        JOIN groups ON groups.id = belong.id_group
                                        JOIN asign ON asign.id_group = groups.id
                                        JOIN events ON events.id = asign.id_event
                                        JOIN types ON types.id = events.id_type
                                        WHERE users.id = '.$id.'
                                        AND 
                                        events.id_type ='.$type.'
                                        ORDER BY events.id DESC')->as_assoc()->execute();

            }
            if (count($query) == 0) {
                return $this->createResponse(400, 'Listado de eventos vacio');
            }

            return $this->createResponse(200, 'Listado de eventos', $query);

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_event()
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

        if (empty($_GET['id'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id) ');
        }

        $id = $_GET['id'];
   
        try {
            
            $event = Model_Events::find($id); 
            if ($event == null) {
                return $this->createResponse(400, 'No existe el evento');

            }
            /*
            $commentsBD = Model_Comments::find('all',array('rows_limit' => 3),
                array('where' => array(array('id_event' ,$id)))
            );*/
            //todo sacar solo 3 comments
            $commentsBD = Model_Comments::find('all',
                array('where' => array(array('id_event' ,$id)))
            );

            foreach ($commentsBD as $key => $comment) {
                $userBD = Model_Users::find($comment->id_user);
                $comment['username'] = $userBD->username;
                $comment['id_user'] = $userBD->id;
                $comment['photo'] = $userBD->photo;
            }

            return $this->createResponse(200, 'Evento y comentarios', array('event'=>$event , 'comments'=> array_reverse(Arr::reindex($commentsBD))));

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

        if (empty($_POST['id'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id) ');
        }

        $id = $_POST['id'];
   
        try {
            
            $event = Model_Events::find($id); 
            if ($event == null) {
                return $this->createResponse(400, 'No existe el evento');
            }

            if ($event->id_user == $user->data->id || $user->data->id_rol == 1) {

                if($event->image != null){

                    $imgEvent = explode("/",$event->image)[8];

                    unlink(DOCROOT . 'assets/img/' . $imgEvent);

                }

                $event->delete();
                return $this->createResponse(200, 'Evento borrado');
            }else{
                return $this->createResponse(401, 'No autorizado');
            }

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_find()
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
        $id = $user->data->id;

        if (empty($_GET['search'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (search) ');
        }

        $search = $_GET['search'];
        $search = '"%'.$search.'%"';

        if (!isset($_GET['type']) )
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (type, 0 -> todos, 1-> eventos, 2-> ofertas trabajo, 3 -> notificaciones, 4 -> noticias) ');
        }

        $type = $_GET['type'];
   
        try 
        {
            /*
            $query = DB::select()->from('belong');
            $query->join('users');
            $query->on('users.id','=',$id);
            $query->join('groups');
            $query->on('groups.id','=','belong.id_group');
            $query->join('asign');
            $query->on('asign.id_group','=','belong.id_group');
            $query->join('events');
            $query->on('events.id','=','asign.id_event');
            $query->where('event.description','LIKE',$search);
            */
            if ($type == 0 ) 
            {
            
            $query = \DB::query('SELECT DISTINCT events.* FROM belong
                                    JOIN users ON belong.id_user = users.id
                                    JOIN groups ON groups.id = belong.id_group
                                    JOIN asign ON asign.id_group = groups.id
                                    JOIN events ON events.id = asign.id_event
                                    WHERE users.id = '.$id.'
                                    AND
                                    events.title LIKE '.$search.'
                                    OR
                                    events.description LIKE'.$search.'
                                    ORDER BY events.id DESC')->as_assoc()->execute();
            }
            else
            {
                $typeDB = Model_Types::find($type);
                if ($typeDB == null) {
                    return $this->createResponse(400, 'Parametro type no valido');
                }
                $query = \DB::query('SELECT DISTINCT events.* FROM belong
                                    JOIN users ON belong.id_user = users.id
                                    JOIN groups ON groups.id = belong.id_group
                                    JOIN asign ON asign.id_group = groups.id
                                    JOIN events ON events.id = asign.id_event
                                    WHERE users.id = '.$id.'
                                    AND
                                    events.id_type = '.$type.'
                                    AND
                                    (events.title LIKE '.$search.'
                                    OR
                                    events.description LIKE'.$search.')
                                    ORDER BY events.id DESC'
                                    )->as_assoc()->execute();

            }
            if (count($query) == 0 )
            {
                return $this->createResponse(400, 'No existen eventos');
            }

            return $this->createResponse(200, 'Listado de eventos', $query);

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_types()
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
   
        try {
            $typesBD = Model_Types::find('all');
            if ($typesBD== null) 
            {
                return $this->createResponse(400, 'No existe ningun tipo');
            }

            return $this->createResponse(200, "Tipos devueltos", Arr::reindex($typesBD));

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_comments()
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
   
        if (empty($_GET['id_event'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id_event) ');
        }
        $id_event= $_GET['id_event'];

        try {
            $eventDB = Model_Events::find($id_event);
            if (empty($eventDB)) {
                return $this->createResponse(400, "No existe el evento");
            }

            $commentsBD = Model_Comments::find('all', array(
            'where' => array(
                array('id_event',$id_event)
                )
            )); 
            if (empty($commentsBD)) {
                return $this->createResponse(400, "No existen comentarios");
            }
            foreach ($commentsBD as $key => $comment) {
                $userBD = Model_Users::find($comment->id_user);
                $comment['username'] = $userBD->username;
                $comment['id_user'] = $userBD->id;
                $comment['photo'] = $userBD->photo;
            }
            return $this->createResponse(200, "Listado de comentarios", Arr::reindex($commentsBD));

        } 
        catch (Exception $e) 
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
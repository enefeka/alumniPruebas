<?php
//require_once '../../../vendor/autoload.php';
use Firebase\JWT\JWT;

class Controller_Users extends Controller_Rest
{
    private $key = 'my_secret_key';
    protected $format = 'json';

    private $urlPro = 'http://h2744356.stratoserver.net/solfamidas/alumniCEV/public/assets/img/';
    private $urlDev = 'http://localhost:8888/alumniCEV-master/public/assets/img/';

    function post_create()
    {

        try {

            if (empty($_POST['email']) || empty($_POST['password']) ) 
            {

              return $this->createResponse(400, 'Parámetros incorrectos ( email, password)');
            }

            if(strlen($_POST['password']) < 5 || strlen($_POST['password']) > 12){
                return $this->createResponse(400, 'La contraseña tiene una longitud no válida.');
            }

            $email = $_POST['email'];
            $password = $_POST['password'];

            if($this->userNotRegistered($email))
            { 

                $newPrivacity = new Model_Privacity(array('phone' => 0,'localization' => 0));
                $newPrivacity->save();
                $props = array('password' => $password, 'id_privacity' => $newPrivacity->id, 'is_registered' => 1);


                $newUser = Model_Users::find('first', array(
                   'where' => array(
                       array('email', $email)

                       ),
                   ));

                $newUser->set($props);
                $newUser->save();

                return $this->createResponse(200, 'Usuario creado', ['user' => $newUser]);

            }
            else
            { //Si el email no es valido ( no esta en la bbdd o ya esta registrado )

                return $this->createResponse(400, 'E-mail no valido o ya esta registrado');
            } 

        }
        catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());

        }      
    }

    function post_changepassword()
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

        if(empty($_POST['lastpassword']) || $_POST['lastpassword'] == "" || empty($_POST['password']) || $_POST['password'] == ""){
            return $this->createResponse(400, 'Faltan parámetros(lastpassword y/o password)');
        }

        $lastPassword = $_POST['lastpassword'];
        $password = $_POST['password'];

        $userDB = Model_Users::find('first', array(
                   'where' => array(
                       array('id', $user->data->id),
                       array('password', $lastPassword)
                       ),
                   ));

        if($userDB == null){
            return $this->createResponse(400, 'La contraseña antigua no es válida');
        }

        $userDB->password = $password;
        $userDB->save();

        return $this->createResponse(200, 'Contraseña modificada', array('user' => $userDB));
    }

    function post_insertUser()
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
        // validar rol de admin
        $user = $this->decodeToken();
        if ($user->data->id_rol != 1) {
            return $this->createResponse(401, 'No autorizado');
        }
        // falta parametro email
        if (empty($_POST['email']) || empty($_POST['id_rol']) || empty($_POST['id_group']) || empty($_POST['name'])) {
            return $this->createResponse(400, 'Falta parametro email, id_rol, id_group, name');
        }
        $email = $_POST['email'];
        $id_rol = $_POST['id_rol'];
        $id_group = $_POST['id_group'];
        $name = $_POST['name'];
        $username = explode("@", $email)[0];
        try {
            $rolDB = Model_Roles::find($id_rol);
            if ($rolDB == null) {
                return $this->createResponse(400, 'Rol no valido (1-> admin, 2-> profesor, 3-> alumno)');
            }
            //grupo 
            $groupDB = Model_Groups::find($id_group);
            if ($groupDB == null) {
                return $this->createResponse(400, 'id_group no valido');
            }

            // validar que no exista ese email en la bbdd
            $userDB = Model_Users::find('first', array(
               'where' => array(
                   array('email', $email),
                   ),
            ));

            if ($userDB != null) 
            {
                return $this->createResponse(400, 'El email ya existe');
            }
            // crear un nueov usuario
            $newUser = new Model_Users(array('email' => $email,'is_registered'=> 0, 'id_rol'=>$id_rol,  'name' => $name,'username'=> $username));
            $newUser->save();

            // usuario a grupo
            $belongDB = new Model_Belong();
            $belongDB->id_user = $newUser->id;
            $belongDB->id_group = $groupDB->id;
            $belongDB->save();

            return $this->createResponse(200, 'Usuario insertado con exito');

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
            return $this->createResponse(400,'Token no encontrado');
        }
        $jwt = apache_request_headers()['Authorization'];
        // valdiar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            return $this->createResponse(400, 'Error de autentificacion');
        }
        // validar rol de admin
        $user = $this->decodeToken();
        if ($user->data->id_rol != 1) {
            return $this->createResponse(401, 'No autorizado');
        }
        // falta parametro email
        if (empty($_POST['id'])) {
            return $this->createResponse(400, 'Falta parametro id');
        }

        $id = $_POST['id'];

        try {
            // validar que no exista ese usuario en la bbdd
            $userDB = Model_Users::find($id);
            if ($userDB == null) 
            {
                return $this->createResponse(400, 'El usuario no existe');
            }

            if($userDB->photo != null){
                $imgUser = explode("/", $users->image[8]);
                unlink(DOCROOT . 'asset/img' . $imgUser);               
            }

            $userDB->delete();
            //return $this->createResponse(400, $userDB);
            return $this->createResponse(200, 'Usuario borrado');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_login()
    {

        if (empty($_GET['email']) || empty($_GET['password']) )
        {
            return $this->createResponse(400, 'Parámetros incorrectos');
        }

        $email = $_GET['email'];
        $password = $_GET['password'];

        $userDB = Model_Users::find('first', array(
           'where' => array(
               array('email', $email),
               array('password', $password)
               ),
           ));

      	if($userDB != null){ //Si el usuario se ha logueado (existe en la BD)

            if ($userDB['id_rol'] != 1) {
                return $this->createResponse(401, 'No autorizado');
            }
      		//Creación de token
      		$time = time();
      		$token = array(
                    'iat' => $time, 
                    'data' => [ 
                    'id' => $userDB['id'],
                    'email' => $email,
                    'username' => $userDB['username'],
                    'password' => $password,
                    'id_rol' => $userDB['id_rol'],
                    'id_privacity' => $userDB['id_privacity'],
                    'group' => $userDB['group']
                    ]
                );

      		$jwt = JWT::encode($token, $this->key);

            return $this->createResponse(200, 'login correcto', ['token' => $jwt, 'user' => $email]);

        }else{

          return $this->createResponse(400, 'Usuario o contraseña incorrectas');

      }
    }

    function post_login()
    {
        try {

            if (empty($_POST['email']) || empty($_POST['password']) )
            {
                return $this->createResponse(400, 'Parámetros incorrectos');
            }
            $email = $_POST['email'];
            $password = $_POST['password'];

            $userDB = Model_Users::find('first', array(
               'where' => array(
                   array('email', $email),
                   array('password', $password)
                   ),
               ));

            if($userDB != null){ //Si el usuario se ha logueado (existe en la BD)

                // si manda coordenadas se guardan
                if (!empty($_POST['lon']) && !empty($_POST['lat']) ) {
                    $lon = $_POST['lon'];
                    $lat = $_POST['lat'];
                    $userDB->lon = $lon;
                    $userDB->lat = $lat;
                    $userDB->save();
                }
                

                //Creación de token
                $time = time();
                $token = array(
                    'iat' => $time, 
                    'data' => [ 
                    'id' => $userDB['id'],
                    'email' => $email,
                    'username' => $userDB['username'],
                    'password' => $password,
                    'id_rol' => $userDB['id_rol'],
                    'id_privacity' => $userDB['id_privacity'],
                    'group' => $userDB['group']
                    ]
                );

                $jwt = JWT::encode($token, $this->key);

                $privacity = Model_Privacity::find($userDB->id_privacity);

                return $this->createResponse(200, 'login correcto', ['token' => $jwt, 'user' => $userDB, 'privacity' => $privacity]);

            }
            else
            {

              return $this->createResponse(400, 'Usuario o contraseña incorrectas');

            }
        } 
        catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
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
        if ($user->data->id_rol != 1 && $user->data->id != $id) {
            return $this->createResponse(401, 'No autorizado');
        }

        try {
            
            $userBD = Model_Users::find($id);
            if ($userBD == null) {
                return $this->createResponse(400, 'No existe el usuario');
            }

            if (!empty($_POST['email']) ) {
                $userBD->email = $_POST['email'];
            }
            if (!empty($_POST['phone']) ) {
                $userBD->phone = $_POST['phone'];
            }
            if (!empty($_POST['birthday']) ) {
                $userBD->birthday = $_POST['birthday'];
            }
            if (!empty($_POST['description']) ) {
                $userBD->description = $_POST['description'];
            }

            if (isset($_POST['phoneprivacity']) && isset($_POST['localizationprivacity'])) {

                if ($_POST['phoneprivacity'] != 0 && $_POST['phoneprivacity'] != 1){
                    return $this->createResponse(400, 'Valor de phoneprivacity no válido, debe ser 0 ó 1');
                }

                if ($_POST['localizationprivacity'] != 0 && $_POST['localizationprivacity'] != 1){
                    return $this->createResponse(400, 'Valor de localizationprivacity no válido, debe ser 0 ó 1');
                }

                $privacity = Model_Privacity::find($userBD->id_privacity);
                $privacity->phone = $_POST['phoneprivacity'];
                $privacity->localization = $_POST['localizationprivacity'];
                $privacity->save();

            }

            if (!empty($_FILES['photo'])) {
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
                        $userBD->photo = $this->urlPro.$file['saved_as'];
                    }
                }
                // and process any errors
                foreach (Upload::get_errors() as $file)
                {
                    return $this->createResponse(500, 'Error al subir la imagen', $file);
                }
            }
            
            if (!empty($_POST['id_rol']) ) {
                $rolDB = Model_Roles::find($_POST['id_rol']);
                if ($rolDB == null) {
                    return $this->createResponse(200, 'Rol no valido');
                }
                $userBD->id_rol = $_POST['id_rol'];
            }
            $userBD->save();
            return $this->createResponse(200, 'Usuario actualizado');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_validateEmail()
    {
        if (empty($_GET['email'])) {
            return $this->createResponse(400, 'Faltan parametros');
        }
        $email = $_GET['email'];
        try {

            $userDB = Model_Users::find('first', array(
            'where' => array(
                array('email', $email),
                array('is_registered', 1)
                )
            )); 

            if($userDB != null){
                return $this->createResponse(200, 'Correo valido',array('email'=>$email, 'id'=>$userDB->id) );
            }else{
                return $this->createResponse(400, 'Email no valido');
            }
        } catch (Exception $e) {
            return $this->createResponse(500, $e->getMessage());
        }
    }
    
    function post_recoverPassword()
    {
       if (empty($_POST['id']) || empty($_POST['password']) ) {
            return $this->createResponse(400, 'Faltan parametros');
        } 
        $id = $_POST['id'];
        $password = $_POST['password'];
        try {

            $userDB = Model_Users::find($id); 
            if($userDB != null){
                $userDB->password = $password;
                $userDB->save();
                return $this->createResponse(200, 'Contraseña cambiada',array('Nueva contraseña'=>$password) );
            }else{
                return $this->createResponse(400, 'Usuario no encontrado');
            }
        } catch (Exception $e) {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_allusers()
    {

        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }

        $jwt = apache_request_headers()['Authorization'];

        // validar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            return $this->createResponse(400, 'Error de autentificacion');
        }

          //$users = Model_Users::find('all');
          $users = Model_Users::query()->related('roles')->get();

          foreach ($users as $keyUsers => $user) 
          {

              foreach ($user->roles as $keyRoles => $value) {

                  $users[$keyUsers][$keyRoles] = $value;
                  unset($users[$keyUsers]['roles']);
              }
              
          }

          return $this->createResponse(200, 'Usuarios devueltos', Arr::reindex($users));
           
    }
    function get_allusersapp()
    {

        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }

        $jwt = apache_request_headers()['Authorization'];

        // validar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            return $this->createResponse(400, 'Error de autentificacion');
        }
        $id= $this->decodeToken($jwt)->data->id;
          //$users = Model_Users::find('all');
          $users = Model_Users::query()->related('roles')->get();

          foreach ($users as $keyUsers => $user) 
          {
            if ($user->is_registered == 0) 
            {
                unset($users[$keyUsers]);
            }              
          }

          unset($users[$id]);

          return $this->createResponse(200, 'Usuarios devueltos', Arr::reindex($users));
            
    }
    function get_friends()
    {

        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }

        $jwt = apache_request_headers()['Authorization'];

        // validar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            return $this->createResponse(400, 'Error de autentificacion');
        }

        $user = $this->decodeToken();
        $id = $user->data->id;

        $query = \DB::query('SELECT * FROM users
                                        JOIN friend ON friend.id_user_send = '.$id.'
                                        AND users.id = friend.id_user_receive
                                        WHERE friend.state = 2
                                        UNION 
                                        SELECT * FROM users
                                        JOIN friend ON friend.id_user_receive = '.$id.'
                                        AND users.id = friend.id_user_send
                                        WHERE friend.state = 2
                                        ')->as_assoc()->execute();

        if(count($query) == 0){
            return $this->createResponse(200, 'El usuario no tiene amigos');
        }

          return $this->createResponse(200, 'Amigos devueltos', $query);
    }

    function get_request()
    {

        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }

        $jwt = apache_request_headers()['Authorization'];

        $user = $this->decodeToken();   

        if (empty($_GET['id_user'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id_user) ');
        }
        $id_user = $_GET['id_user'];
        // validar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            return $this->createResponse(400, 'Error de autentificacion');
        }

        $friend = Model_Friends::find('first', array(
                        'where' => array(
                            array('id_user_send', $user->data->id),
                            array('id_user_receive', $id_user),
                            'or' => array(
                            array('id_user_send', $id_user),
                            array('id_user_receive', $user->data->id)
                        )
                        ),
                        
                        )); 

        if ($friend != null){
            return $this->createResponse(200, 'Petición mostrada', array('request' => $friend));
        }else{
            return $this->createResponse(200, 'No hay petición entre los usuarios');
        }
    }

    function get_requests()
    {

        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }

        $jwt = apache_request_headers()['Authorization'];
  
        // validar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            return $this->createResponse(400, 'Error de autentificacion');
        }

        $user = $this->decodeToken();

        $friends = \DB::query('SELECT * FROM users
                                        JOIN friend ON friend.id_user_send = '.$user->data->id.'
                                        AND users.id = friend.id_user_receive
                                        UNION 
                                        SELECT * FROM users
                                        JOIN friend ON friend.id_user_receive = '.$user->data->id.'
                                        AND users.id = friend.id_user_send
                                        
                                        ')->as_assoc()->execute();

        if (count($friends) > 0){
            return $this->createResponse(200, 'Peticiónes devueltas', array('requests' => $friends));
        }else{
            return $this->createResponse(400, 'No hay peticiones de amistad');
        }
    }

    function post_sendRequest()
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

        if (empty($_POST['id_user'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id_user) ');
        }
        $id_user = $_POST['id_user'];

        try {
            
            
            $userBD = Model_Users::find('first', array(
            'where' => array(
                array('id', $id_user),
                ),
            )); 

            if ($userBD == null) 
            {
                return $this->createResponse(400, 'No existe el usuario');
            }
            
            $friend = Model_Friends::find('first', array(
                        'where' => array(
                            array('id_user_receive', $user->data->id),
                            array('id_user_send', $id_user),
                            'or' => array(
                                array('id_user_receive', $id_user),
                                array('id_user_send', $user->data->id))
                            ),
            )); 
            if ($friend != null) 
            {
                return $this->createResponse(400, 'Ya existe una petición existente entre ambos usuarios o ya sois amigos');
            }

            $props = array('id_user_receive' => $id_user,'id_user_send' => $user->data->id ,'state' => 1);

            $newfriend = new Model_Friends($props);
            $newfriend->save();

            return $this->createResponse(200, 'Peticion enviada',['user' => $userBD]);

        } 
        catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function post_responseRequest()
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

        if (empty($_POST['type']) || empty($_POST['id_user'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id_user o type) ');
        }

        if ($_POST['type'] != 2 && $_POST['type'] != 3) {
            return $this->createResponse(400, 'El tipo enviado no es valido');

        }

        $id_user = $_POST['id_user'];
        $type = $_POST['type'];

        try {
            
            
            $friend = Model_Friends::find('first', array(
            'where' => array(
                array('id_user_receive', $user->data->id),
                array('id_user_send', $id_user),
                array('state',1)
                ),
            )); 

            if ($friend == null) {
                return $this->createResponse(400, 'No es posible dejar de seguir al usuario');
            }

            $friend->state=$type;
            $friend->save();

            if ($type==2) {
                return $this->createResponse(200, 'Solicitud Aceptada');
            }else{
                $friend->delete();
                return $this->createResponse(200, 'Solicitud Denegada');
                
            }
            

        } catch (Exception $e) 
        {
            if($e->getCode() == 23000){
                return $this->createResponse(400, 'Ya existe una petición existente entre ambos usuarios');
            }

            return $this->createResponse(500, $e->getMessage());
        }
    
    }

    function post_deleteFriend()
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

        if (empty($_POST['id_user'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id_user) ');
        }
        $id_user = $_POST['id_user'];

        try {
            
            
            $userBD = Model_Users::find('first', array(
            'where' => array(
                array('id', $id_user),
                ),
            )); 

            if ($userBD == null) {
                return $this->createResponse(400, 'No existe el usuario');
            }

            $friend = Model_Friends::find('first', array(
                        'where' => array(
                            array('id_user_receive', $user->data->id),
                            array('id_user_send', $id_user),
                            array('state',2),
                            'or' => array(
                                array('id_user_receive', $id_user),
                                array('id_user_send', $user->data->id),
                                array('state',2))
                            ),
            )); 

            if($friend == null){
                return $this->createResponse(400, 'No se puede dejar de seguir al usuario');
            }
            
            $friend->delete();

        return $this->createResponse(200, 'Ya no es Amigo',['user' => $userBD]);

        } catch (Exception $e) 
        {
            

            return $this->createResponse(500, $e->getMessage());
        }
    }

    function post_cancelRequest()
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

        if (empty($_POST['id_user'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id_user) ');
        }

        $id_user = $_POST['id_user'];

        try {
            
            $userBD = Model_Users::find('first', array(
            'where' => array(
                array('id', $id_user),
                ),
            )); 

            if ($userBD == null) {
                return $this->createResponse(400, 'No existe el usuario');
            }

            $friend = Model_Friends::find('first', array(
                        'where' => array(
                            array('id_user_send', $user->data->id),
                            array('id_user_receive', $id_user),
                            array('state',1)
                            ))); 

            if($friend == null){
                return $this->createResponse(400, 'No has enviado una petición de amistad a este usuario');
            }
            
            $friend->delete();

            return $this->createResponse(200, 'Petición cancelada',['user' => $userBD]);

        } catch (Exception $e) 
        {
            

            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_user()
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
        
        if (empty($_GET['username'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (username) ');
        }

        $username = $_GET['username'];

        try {
            
            
            $usersBD = Model_Users::find('all', array(
            'where' => array(
                array('username' ,'LIKE' ,'%'.$username.'%'),
                ),
            )); 

            if ($usersBD == null) {
                return $this->createResponse(400, 'No existe el usuario');
            }
            return $this->createResponse(200, 'Listado de usuarios', Arr::reindex($usersBD));

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }
    function get_finduserapp()
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
        
        if (empty($_GET['search'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (search) ');
        }

        $search = $_GET['search'];
        //$search = "'%".$search."%'";
        try {

            $usersBD = Model_Users::find('all', array(
            'where' => array(
                array('is_registered' ,1),
                array('username' ,'LIKE' ,'%'.$search.'%'),
                'or' =>
                array('name' ,'LIKE' ,'%'.$search.'%'),
                ),
            )); 
            if (count($usersBD) < 1) {
                return $this->createResponse(400, 'No hay usuarios');
            }
            return $this->createResponse(200, 'Listado de usuarios', $usersBD);

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_find_friend()
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
        
        if (empty($_GET['search'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (search) ');
        }

        $search = $_GET['search'];
        $search = "'%".$search."%'";
        try {
            
            $query = \DB::query('SELECT DISTINCT users.* FROM users
                                    JOIN friend
                                    ON friend.id_user_send = users.id 
                                    OR friend.id_user_receive = users.id
                                    WHERE friend.state = 2
                                    AND
                                    users.id != '.$user->data->id.'
                                    AND (
                                        users.username LIKE '.$search.'
                                        OR
                                        users.name LIKE '.$search.'
                                    )
                                    AND
                                    (
                                        friend.id_user_send = '.$user->data->id.'
                                        OR
                                        friend.id_user_receive = '.$user->data->id.'
                                        )')->as_assoc()->execute();
            if (count($query) < 1) {
                return $this->createResponse(400, 'No hay usuarios');
            }
            return $this->createResponse(200, 'Listado de usuarios', $query);

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_userbyid()
    {

        try {

            $jwt = apache_request_headers()['Authorization'];

            if($this->validateToken($jwt))
            {

                if (empty($_GET['id']))
                {
                    return $this->createResponse(400, 'Parámetros incorrectos');
                }

                $user = $this->decodeToken();

                $id = $_GET['id'];

                $userDB = Model_Users::find($id);

                if($userDB == null)
                {
                    return $this->createResponse(400, 'El usuario no existe');
                }

                $privacity = Model_Privacity::find($userDB->id_privacity);

                $friend = Model_Friends::find('first', array(
                        'where' => array(
                            array('id_user_receive', $user->data->id),
                            array('id_user_send', $id),
                            'or' => array(
                            array('id_user_receive', $id),
                            array('id_user_send', $user->data->id)
                            )
                            ),
                        ));

                return $this->createResponse(200, 'Usuario devuelto', array('user' => $userDB, 'privacity' => $privacity, 'friend' => $friend));
                
                }
            else
            {
                $this->createResponse(400, 'No tienes permiso para realizar esta acción');
            }

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

    function userNotRegistered($email)
    {

        $userDB = Model_Users::find('first', array(
            'where' => array(
                array('email', $email),
                array('is_registered', 0)
                )
            )); 

        if($userDB != null){
            return false;
        }else{
            return true;
        }
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



/*
    


    

    function post_borrar()
    {

        $jwt = apache_request_headers()['Authorization'];

        if($this->validateToken($jwt)){
          $id = $_POST['id'];

          $usuario = Model_Users::find($id);
          $usuario->delete();

          $this->createResponse(200, 'Usuario borrado', ['usuario' => $usuario]);

      }else{

          $this->createResponse(400, 'No tienes permiso para realizar esta acción');

      }
    }

    function post_edit()
    {
        $jwt = apache_request_headers()['Authorization'];

        if($this->validateToken($jwt)){
          $id = $_POST['id'];
          $search = $_POST['username'];
          $password = $_POST['password'];

          $usuario = Model_Users::find($id);
          $usuario->username = $username;
          $usuario->password = $password;
          $usuario->save();

          $this->createResponse(200, 'Usuario editado', ['usuario' => $usuario]);

      }else{

          $this->createResponse(400, 'No tienes permiso para realizar esta acción');

      }
    }
*/
}
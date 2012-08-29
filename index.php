<?php
header('Content-Type: text/html; charset=UTF-8');
require __DIR__.'/vendor/Slim/Slim.php';
require __DIR__.'/views/TwigView.php';
require __DIR__.'/vendor/ActiveRecord/ActiveRecord.php';
require __DIR__.'/vendor/Ladybug/Autoloader.php';
require __DIR__.'/vendor/GUMP/gump.class.php';
require __DIR__.'/vendor/Facebook-sdk/facebook.php';
require __DIR__.'/vendor/Twitter/twitter.class.php';
require __DIR__.'/vendor/PHPMailer/class.phpmailer.php';
require __DIR__.'/vendor/elFinder/connector.php';
require __DIR__.'/vendor/Toolbox.php';
require __DIR__.'/vendor/DirectoryLister.php';
require __DIR__.'/vendor/upload.class.php';
require __DIR__."/vendor/pChart/class/pData.class.php";
require __DIR__."/vendor/pChart/class/pDraw.class.php";
require __DIR__."/vendor/pChart/class/pPie.class.php";
require __DIR__."/vendor/pChart/class/pImage.class.php";
require __DIR__.'/vendor/Faker/autoload.php';

Ladybug\Autoloader::register();

$connections = array(
	'dev' => 'mysql://root:root@localhost/posgrado',
	'prod' => 'mysql://'.getenv('MYSQL_USERNAME').':'.getenv('MYSQL_PASSWORD').'@'.getenv('MYSQL_DB_HOST').'/'.getenv('MYSQL_DB_NAME').';charset=utf8'
);
ActiveRecord\Config::initialize(function ($cfg) use ($connections){
	$cfg->set_model_directory('models');
	$cfg->set_connections($connections); 
	$cfg->set_default_connection('dev');
});


TwigView::$twigOptions = array('charset' => 'utf-8','strict_variables' => true);
TwigView::$twigDirectory = __DIR__.'/vendor/Twig/';
TwigView::$twigExtensions = array('Extension_Twig_Slim');

$app = new Slim(array(
    'debug' => true,
    'view' => 'TwigView'
));


/* =======================
 * ======= SESIÓN ========
 * =======================*/
/*$app->get('/login/', function () use($app) {
	$app->render('login.html');
})->name('login');*/

$app->post('/login/', function() use ($app) {
	$validator = new GUMP();
	ladybug_dump($_POST);
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'usuario'    => 'required|alpha_dash|max_len,100',
		'password'    => 'required|max_len,100',
	);

	$filters = array(
		'usuario' 	  => 'trim|sanitize_string',
		'password'	  => 'trim|md5'
	);
	$_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	ladybug_dump($validated);
	if($validated === true) {
		$user = Usuario::find_by_usuario($_POST['usuario']);
		if(is_object($user)){
			if($user->password === $_POST['password']){
				if($user->activo == 1){
					$expiration = (isset($_POST['cookie']) ? '1 month' : '2 hours');
					$app -> setCookie('userId', $user -> id, $expiration);	
				} else {
					$flash = array(
						"title" => "ERROR",
						"msg" => "Su cuenta ha sido suspendida. Pongase en contacto con el administrador.",
						"type" => "error",
						"fade" => 0
					);
					$app -> flash("flash", $flash);
					$app -> flashKeep();
				}
				$app -> redirect($app->urlFor('home'));
			} else {
				$flash = array(
					"title" => "ATENCIÓN",
					"msg" => "Contraseña Incorrecta",
					"type" => "warning",
					"fade" => 1
				);
				$app -> flash("flash", $flash);
				$app -> flashKeep();
				$app -> redirect($app->urlFor('home'));
			}	
		} else {
			$flash = array(
				"title" => "ERROR",
				"msg" => "El usuario indicado no existe.",
				"type" => "error",
				"fade" => 1
			);
			$app -> flash("flash", $flash);
			$app -> flashKeep();
			$app -> redirect($app->urlFor('home'));
		}
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app -> flashKeep();
		$app -> redirect($app->urlFor('home'));
	}
})->name('login-post');

$app->get('/logout/', function() use ($app) {
	$userId = $app -> getCookie('userId');
	if (isset($userId)) {
		$app -> setCookie('userId', null);
	}
	$app -> redirect($app->urlFor('home'));
})->name('logout');

/* =======================
 * ==== ADMINISTRADOR ====
 * =======================*/
$app->get('/admin/', function() use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin")
	);
	$data['user'] = isAllowed("Administrador",false);
	$app->render('dashboard.html',$data);
})->name('admin');

$app->get('/admin/usuarios/', function() use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Gestor de Usuarios", "alias" => "admin-usuarios")
	);
	$data['user'] = isAllowed("Administrador",false);
	$data['usuarios'] = Usuario::find('all', array('order'=>'activo desc','include' => array('personal','ur','contacto')));
	$data['roles'] = Rol::find('all', array('order' => 'nombre asc'));
	$app->render('users.html', $data);
})->name('admin-usuarios');

$app->post('/nuevo-usuario/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'usuario'    => 'required|alpha_dash|max_len,100',
		'email'    => 'required|valid_email',
	);

	$filters = array(
		'usuario' 	  => 'trim|sanitize_string',
		'email'	  => 'trim|sanitize_email'
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === true) {
		$tb = new Toolbox();
		$password = $tb->generate_password(7,7);
		$ok = Usuario::transaction(function() use($post,$tb,$password){
			//TODO Verificar que el nombre de usuario no exista antes de guardar
			$user = Usuario::find_by_login($_POST['usuario']);
			if(is_object($user)) return false;
			
			$usuario = new Usuario();
			$usuario->usuario = $_POST['usuario'];
			$usuario->password = md5($password);
			$usuario->activo = 1;
			$usuario->creado = time();
			$usuario->actualizado = time();
			$usuario->save();
			if(!$usuario)return false;
			
			$ur = new UsuariosRoles();
			$ur->rol_id = $_POST['rol'];
			$ur->usuario_id = $usuario->id;
			$ur->save();
			if(!$ur)return false;
			
			$personal = new Personal();
			$personal->usuario_id = $usuario->id;
			$personal->save();
			if(!$personal)return false;
			
			$academico = new Academico();
			$academico->usuario_id = $usuario->id;
			$academico->save();
			if(!$academico)return false;
			
			$laboral = new Laboral();
			$academico->usuario_id = $usuario->id;
			$laboral->save();
			if(!$laboral)return false;
			
			$contacto = new Contacto();
			$contacto->usuario_id = $usuario->id;
			$contacto->email = $_POST['email'];
			$contacto->fijo = "";
			$contacto->movil = "";
			$contacto->contactar = 0;
			$contacto->save();
			if(!$contacto)return false;
			
			return true;
		});
		if($ok){
#TODO Verificar recepcion de correo con datos de acceso
#TODO Generar interfaz para activación de cuenta y cambio de contraseña
			$mailData = array(
				"to" => $_POST['email'],
				"subject" => "Activación de cuenta",
				"body" => "",
				"from" => "posgradomsc@gmail.com",
				"alias" => "Posgrado MSC IT Cd. Victoria"
			);
			$mailed = sendMail($to, $subject, $body, $from, $alias);
			if($mailed){
				$msg = "El usuario se ha creado con éxito, y se ha enviado notificación al correo indicado.";
			} else{
 				$msg = "El usuario se ha creado con éxito, pero hubo problemas al enviar notificación al correo indicado.";
			}
			$flash = array(
				"title" => "OK",
				"msg" => $msg,
				"type" => "success",
				"fade" => 1
			);	
		} else {
			$flash = array(
				"title" => "ERROR",
				"msg" => "Ha sucedido un error inesperado, intente nuevamente.",
				"type" => "error",
				"fade" => 0
			);
		}
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('admin-usuarios'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('admin-usuarios'));
	}
})->name('nuevo-usuario-post');

$app->post('/actualiza-usuario/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$password = $_POST['password'];
	$rules = array(
		'usuario-edit'    => 'required|alpha_dash|max_len,100',
		'email-edit'    => 'required|valid_email',
	);

	$filters = array(
		'usuario-edit' 	  => 'trim|sanitize_string',
		'email-edit'	  => 'trim|sanitize_email',
		'password' => 'trim|md5'
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === true) {
		$ok = Usuario::transaction(function() use($post,$password,$id){
			$usuario = Usuario::find($id);
			if($_POST['usuario-edit'] != ""){
				$usuario->usuario = $_POST['usuario-edit'];
			}
			if($password != ""){
				$usuario->password = md5($password);	
			}
			$usuario->actualizado = time();
			$usuario->save();
			if(!$usuario)return false;
			
			if($_POST['rol-edit'] != 0){
				$ur = UsuariosRoles::find_by_usuario_id($id);
				$ur->rol_id = $_POST['rol-edit'];
				$ur->save();
				if(!$ur)return false;
			}
			
			if($_POST['email-edit'] != ""){
				$contacto = Contacto::find_by_usuario_id($id);
				$contacto->email = $_POST['email-edit'];
				$contacto->save();
				if(!$contacto)return false;
			}
			
			return true;
		});
		if($ok){
#TODO Enviar el correo al usuario con los datos de acceso actualizados
#TODO Enviar notificacion de datos actualizados.
			$flash = array(
				"title" => "OK",
				"msg" => "El usuario se ha actualizado con éxito.",
				"type" => "success",
				"fade" => 1
			);	
		} else {
			$flash = array(
				"title" => "ERROR",
				"msg" => "Ha sucedido un error inesperado, intente nuevamente.",
				"type" => "error",
				"fade" => 0
			);
		}
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('admin-usuarios'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('admin-usuarios'));
	}	
})->name('actualiza-usuario-post');

$app->get('/cambiar-status-usuario/:id/', function($id) use($app){
	$usuario = Usuario::find($id);
	if($usuario->activo == 0){
		$usuario->activo = 1;
		$verb = "activado";
	} else {
		$usuario->activo = 0;
		$verb = "desactivado";
	}
	$usuario->save();
	
	$flash = array(
		"title" => "OK",
		"msg" => "El usuario ha sido $verb correctamente.",
		"type" => "info",
		"fade" => 1
	);
	$app -> flash("flash", $flash);
	$app->flashKeep();
	$app->redirect($app->urlFor('admin-usuarios'));
})->name('cambiar-status-usuario');

$app->get('/admin/roles/', function() use($app){
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Gestor de Roles", "alias" => "admin-roles")
	);
	$data['user'] = isAllowed("Administrador",false);
//TODO Crear interfaz para administrar el catálogo de roles
})->name('admin-roles');

#NOT USED
$app->get('/admin/permisos/', function() use($app){
	$data['user'] = isAllowed("Administrador",false);
})->name('admin-permisos');

$app->get('/admin/aspirantes/', function() use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Gestor de Aspirantes", "alias" => "admin-aspirantes")
	);
	$data['user'] = isAllowed("Administrador",false);
	$rolAspirante = Rol::find_by_nombre("Aspirante");
	$idAspirantes = UsuariosRoles::find_all_by_rol_id($rolAspirante->id);
	$ids = array();
	foreach ($idAspirantes as $ia) { $ids[] = $ia->usuario_id; }
	$data['aspirantes'] = "";
	if(!empty($ids)){
		$data['aspirantes'] = Usuario::find_all_by_id($ids, array('order'=>'creado asc','include' => array('personal','ur','contacto')));	
	}
	$app->render('aspirantes.html', $data);
})->name('admin-aspirantes');

$app->get('/procesar-aspirante/:action/:id/', function($action,$id) use($app){
	$rolAlumno = Rol::find_by_nombre("Alumno");
	$rolNoAceptado = Rol::find_by_nombre("No aceptado");
	$ur = UsuariosRoles::find_by_usuario_id($id);
//TODO Agregar interaccion para modificar estadistica de matriculacion y genero
	if($action == "aceptar"){
		$ur->rol_id = $rolAlumno->id;
		$verb = "aceptado";
	} else {
		$ur->rol_id = $rolNoAceptado->id;
		$verb = "no aceptado";
	}
	$ur->save();
	$flash = array(
		"title" => "OK",
		"msg" => "Aspirante $verb.",
		"type" => "info",
		"fade" => 1
	);
	$app -> flash("flash", $flash);
	$app->flashKeep(); 
	$app->redirect($app->urlFor('admin-aspirantes'));
})->name('procesar-aspirante');

$app->get('/admin/archivos/', function() use($app){
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Gestor de Archivos", "alias" => "admin-archivos")
	);
	$data['user'] = isAllowed("Administrador",false);
	$app->render('filemanager.html', $data);
})->name('admin-archivos');

$app->map("/admin/elFinder/", function() use($app){
	$opts = array(
		// 'debug' => true,
		'roots' => array(
			array(
				'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
				'path'          => './Drive/',         // path to files (REQUIRED)
				'URL'           => $app->urlFor('home'). '/Drive/', // URL to files (REQUIRED)
				'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
			)
		)
	);
	$connector = new elFinderConnector(new elFinder($opts));
	$connector->run();
})->via('GET', 'POST')->name('elFinder');

$app->get('/explorador-archivos/', function() use($app){
	$data['user'] = isAllowed("Verificador",false);
	$dir = ($dir == "/Drive/") ? $dir : urldecode($dir);
	$dir = "/Drive/";  
	$dir = __DIR__.$dir;
	$lister = new DirectoryLister();
	$data['directory'] = $lister->listDirectory($dir);
	$data['breadcrumbs'] = $lister->listBreadcrumbs();
	$app->render('explorer.html', $data);
})->name('explorador-archivos');

$app->post('/dirlist/', function() use($app){
	$root = __DIR__;
	$_POST['dir'] = urldecode($_POST['dir']);
	if( file_exists($root . $_POST['dir']) ) {
		$files = scandir($root . $_POST['dir']);
		natcasesort($files);
		$tb = new Toolbox();
		if( count($files) > 2 ) { /* The 2 accounts for . and .. */
			echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
			// All dirs
			foreach( $files as $file ) {
				$begin = $file[0];
				if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file)) {
					if($begin != "."){
						$realPath = $root.$_POST['dir'].$file;
						echo "<li class=\"directory collapsed\">";
						echo "<a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">";
						echo htmlentities($file) . "</a></li>";						
					}
				}
			}
			// All files
			foreach( $files as $file ) {
				if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
					$ext = preg_replace('/^.*\./', '', $file);
					$begin = $file[0];
					if($begin != "."){
						$realPath = $root.$_POST['dir'].$file;
						$dir = substr($_POST['dir'], 1);
						$URI = $app->urlFor('file-reader',array('file' => base64_encode($dir.$file)));
						echo "<li class=\"file ext_$ext\">";
						echo "<a href=\"$URI\" rel=\"$URI\">";
						echo htmlentities($file)."</a></li>";
					}
				}
			}
			echo "</ul>";	
		}
	}
})->name('dirlist');

$app->get('/download/:file', function($file) use($app){
	$file = base64_decode($file);
	$file = __DIR__."/".$file;
	if(!file_exists($file)){
	 $flash = array(
		"title" => "ERROR",
		"msg" => "No se encontro el archivo $file",
		"type" => "error",
		"fade" => 1
	);
	$app -> flash("flash", $flash);
	$app->flashKeep();
	$app->redirect($app->urlFor('explorador-archivos'));
	} else {
	     $ext = pathinfo($file, PATHINFO_EXTENSION);
		 $mime = mime($ext);
	     header("Cache-Control: public");
		 header("Content-Type: $mime");
	     header("Content-Description: File Transfer");
	     header("Content-Disposition: attachment; filename=".basename($file));
	     header("Content-Transfer-Encoding: binary");
	     readfile($file);
 	}
})->name('file-reader');

$app->post('/uploader/',function() use($app){
	$upload_handler = new UploadHandler();
	header('Pragma: no-cache');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Content-Disposition: inline; filename="files.json"');
	header('X-Content-Type-Options: nosniff');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
	header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');
	switch ($_SERVER['REQUEST_METHOD']) {
	    case 'OPTIONS':
	        break;
	    case 'HEAD':
	    case 'GET':
	        $upload_handler->get();
	        break;
	    case 'POST':
	        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
	            $upload_handler->delete();
	        } else {
	            $upload_handler->post();
	        }
	        break;
	    case 'DELETE':
	        $upload_handler->delete();
	        break;
	    default:
	        header('HTTP/1.1 405 Method Not Allowed');
	}
})->name('uploader');

#XXX Graficas
require 'graphs.php';
/*$app->get('/admin/estadisticas/', function() use($app){
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Estadísticas", "alias" => "admin-estadisticas")
	);
	$data['user'] = isAllowed("Administrador", false);
	$app->render('statistics.html',$data);
})->name('admin-estadisticas');*/

$app->get('/admin/secciones/', function() use($app){
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Gestor de Secciones", "alias" => "admin-secciones")
	);
	$data['user'] = isAllowed("Administrador", false);
	$data['secciones'] = $sections = Seccion::find('all', array('order' => 'nombre asc'));
/*	$baseSections = array('Antecedentes','Misión y Visión','Objetivos','Logros y Reconocimientos',
	'Líneas de Generación y Aplicación de Conocimiento','Vinculación','Proceso de Admisión',
	'Perfil de Ingreso','Perfil de Egreso','Curso Propedéutico','Material de Apoyo',
	'Requisitos para Obtención de Grado','Líneas de Investigación');
	$secs = array();
	foreach ($sections as $s) {
		$secs[] = $s->nombre;
	}
	if(count($baseSections) != count($secs)){
		$tb = new Toolbox();
		foreach ($baseSections as $bs) {
			if(!in_array($bs,$secs)){
				$s = new Seccion();
				$s->nombre = $bs;
				$s->slug = $tb->slugify($bs);
				$s->contenido = "";
				$s->actualizado = time();
				$s->save();
				unset($s);
			}
		}
	}
*/	
	foreach ($sections as $seccion) {
		$data['sections'][$seccion->id] = replace_hashes($seccion->contenido);
	}
	$app->render('secciones.html', $data);
})->name('admin-secciones');

$app->get('/admin/secciones/editor/:slug', function($slug) use($app){
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Gestor de Secciones", "alias" => "admin-secciones"),
		array("name" => "Editor", "alias" => "editor-seccion")
	);
	$data['secciones'] = Seccion::find('all', array('order' => 'nombre asc'));
	$data['seccion'] = Seccion::find_by_slug($slug);
	
	$app->render('editor-seccion.html', $data);
})->name('editor-seccion');

$app->post('/actualiza-seccion/:id/', function($id) use($app){
	$validator = new GUMP();
	//$_POST = $validator->sanitize($_POST);
	$rules = array(
	);

	$filters = array(
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === true) {
		$seccion = Seccion::find($id);
		$seccion->contenido = $_POST['contenido'];
		$seccion->contenedor = $_POST['contenedor'];
		$seccion->actualizado = time();
		$seccion->save();
		$flash = array(
			"title" => "OK",
			"msg" => "La seccion se ha actualizado con éxito.",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
	}
	$app->flashKeep();
	$app->redirect($app->urlFor('admin-secciones'));
})->name('actualiza-seccion-post');

$app->get('/admin/noticias/', function() use($app){
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Gestor de Noticias", "alias" => "admin-noticias")
	);
	$data['user'] = isAllowed("Administrador", false);
})->name('admin-noticias');

$app->get('/admin/catalogos/', function() use($app){
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Catálogos", "alias" => "admin-catalogos")
	);
	$data['user'] = isAllowed("Administrador", false);
	$app->render('catalogos.html', $data);
})->name('admin-catalogos');

/* =======================
 * ======= PUBLICO =======
 * =======================*/
$app->get('/estructura-plan-estudios/', function() use($app){
	$data['optativas'] = Materia::find_by_tipo(0);
	$data['tesis'] = Materia::find_by_nombre('Tesis');
	$data['bd'] = Materia::find_by_nombre('Base de Datos');
	$data['is'] = Materia::find_by_nombre('Ingenieria de Software');
	$data['teoria'] = Materia::find_by_nombre('Teoria de la Computacion');
	$data['modelado'] = Materia::find_by_nombre('Modelado Conceptual de Aplicaciones Web');
	$data['seminario'] = Materia::find_by_nombre('seminario');
	$app->render('reticula02.html',$data);
})->name('plan-estudios');

$app->get('/estadisticas/nucleo-academico/', function() use($app) {
    $data['rol'] = Rol::find_by_nombre('Docente');
    $rolus = UsuariosRoles::find_by_rol_id($data['rol']->id);
    $data['usuarios'] = Usuario::find_all_by_id($rolus->usuario_id,array('include'=> array('personal','docente')));
    $app->render('nucleoacademico.html',$data);
})->name('nucleo-academico');

$app->get('/estadisticas/productividad-academica/', function() use($app) {
    $rol = Rol::find_by_nombre('Docente');
    $rolus = UsuariosRoles::find_all_by_rol_id($rol->id);
    $idusuarios = array();
    foreach ($rolus as $u) {
        $idusuarios[] = $u->id;
    }
	//FIXME Error encontrado al tratar de cargar publicaciones, revisar
    $usuarios = $data['usuarios'] = Usuario::find($idusuarios,array('include'=> array('personal','publicaciones')));
    $publicaciones = array();
    foreach ($usuarios as $usuario) {     
        foreach ($usuario->publicaciones as $pub) {
            $nombrecompleto=$usuario->personal->nombre." ".$usuario->personal->paterno." ".$usuario->personal->materno;
            $publicaciones[$pub->tipo][$nombrecompleto][] = $pub;
        }
    }
    $data['pub'] = $publicaciones;
    ladybug_dump($data);
    //$app->render('productividadacademica.html',$data);
})->name('productividad-academica');

$app->get('/calendario/', function() use($app){
	//TODO Calendario
})->name('calendario');

$app->get('/relacion-aceptados/', function() use($app){
	//TODO Relacion Aceptados
	$app->render('relacion-aceptados.html');
})->name('relacion-aceptados');

$app->get('/publicaciones/', function() use($app){
	$user['usuarios'] = Usuario::find_by_id('1');
	$data['publicaciones']= Publicacion::find_all_by_autor($user['usuarios']->id); 
	$app->render('publicaciones.html',$data);
})->name('publicaciones');

$app->get('/egresados/', function() use($app){
	//TODO Egresados
})->name('egresados');

$app->get('/estadisticas/matriculacion/', function() use($app){
	$app->render('EstadisticaMatriculacion.html');
})->name('matriculacion');

$app->get('/registro-aspirante/', function() use($app){
	//TODO Registro Aspirante
})->name('registro-aspirante');

/* =======================
 * ======= DOCENTE =======
 * =======================*/

$app->get('/docente/', function() use($app){	
     $app->render('docente.html');
})->name('docente');

$app->get('/docente/tesistas/', function() use($app){
	$data['user'] = isAllowed("Docente", false);
	$app->render('gestTesista02.html');
})->name('docente-tesistas');

$app->get('/docente/eventos/', function() use($app){
	$user ['usuarios'] = Usuario::find_by_id('1');
	$data['eventos'] = Evento::find_all_by_autor($user['usuarios']->id); 
	$app->render('eventosDoc.html',$data);
})->name('eventosDoc');

$app->post('/nuevo-tesista/',function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
        'lineaInv'    => 'required|alpha',
        'tesista'    => 'required|alpha',
        'nombreTesista' => 'required|alpha',
        'ingreso' => 'required',
	);
    $filters = array(
        'nombreTesista' => 'trim',
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if($validated === true) {
    	   
    } else {
		
    }
})->name('nuevo-tesista-post');

$app->get('/docente/perfil/', function() use($app) {
	$data['user'] = isAllowed("Docente", false);
    $data['usuario'] = Usuario::find($userId,array('include' => array('academico','personal','contacto','pg','laboral','docente')));
    $data['contacto'] = $data['usuario']->contacto;
    $data['personal'] = $data['usuario']->personal;
    $data['academico'] = $data['usuario']->academico;
    $data['docente'] = $data['usuario']->docente;
    $data['laboral'] = $data['usuario']->laboral;
    $data['posgrado'] = $data['usuario']->pg;
    $data['instituciones'] = Institucion::all();
    $data['estados'] = Estado::all();
    $data['municipios'] = Municipio::all();
    $data['localidades'] = Localidad::all();
    $data['snis'] = SNI::all();
    $data['promeps'] = PROMEP::all();
    
    $app->render('perfildocente.html',$data);
})->name('perfil-docente');

$app->post('/nuevo-subir-doc/',function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'tesis'    => 'required',
		'exposicion'    => 'required',
	);
    $filters = array(
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if($validated === true) {
                
    } else {
        
    }
})->name('nuevo-subir-doc-post');

/* =======================
 * === ALUMNO/ASPIRANTE ==
 * =======================*/
$app->get('/editor-perfil/', function() use($app) {
	$data['user'] = isAllowed(array('Alumno','Aspirante'), false);
	$data['usuario'] = Usuario::find($userId, array('include' => array('academico','personal','contacto','pg','laboral','docente')));
	$data['contacto'] = $data['usuario']->contacto;
	$data['personal'] = $data['usuario']->personal;
	$data['academico'] = $data['usuario']->academico;
	$data['docente'] = $data['usuario']->docente;
	$data['laboral'] = $data['usuario']->laboral;
	$data['posgrado'] = $data['usuario']->pg;
	$data['instituciones'] = Institucion::all();
	$data['estados'] = Estado::all();
	$data['municipios'] = Municipio::all();
	$data['localidades'] = Localidad::all();
	$data['sni'] = SNI::all();
	$data['promep'] = PROMEP::all();
    
	$app->render('perfilaspirantes.html', $data);
})->name('perfil');

$app->post('/nuevo-datos-personales/',function() use($app){
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'nombre'    => 'required|alpha',
        'ap'    => 'required|alpha',
        'am' => 'required|alpha',
        'nacimiento' => 'required',
        'sexo' => 'required|numeric',
        'calle' => 'alpha',
        'numex' => 'required|numeric|max_len,4|min_len,1',
        'numint' => 'numeric|max_len,4|min_len,1',
        'colonia' => 'alpha',
        'cp' => 'required|numeric|max_len,5|min_len,5',
        'nlocalidad' => 'numeric',
        );
    $filters = array(
        'nombre'    => 'trim',
        'ap'    => 'trim',
        'am' => 'trim',
        'sexo' => 'trim',
        'calle' => 'trim',
        'numex' => 'trim',
        'numint' => 'trim',
        'colonia' => 'trim',
        'cp' => 'trim',
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if($validated === true) {
       $perfilpersonal =  new Personal();
       $perfilpersonal->nombre = $_POST['nombre'];
       $perfilpersonal->paterno = $_POST['ap'];
       $perfilpersonal->materno = $_POST['am'];
       $perfilpersonal->fdn = $_POST['nacimiento'];
       $perfilpersonal->sexo = $_POST['sexo'];
       $perfilpersonal->procedencia = $_POST['nlocalidad'];
       $perfilpersonal->calle = $_POST['calle'];
       $perfilpersonal->num_ext = $_POST['numex'];
       $perfilpersonal->num_int = $_POST['numint'];
       $perfilpersonal->colonia = $_POST['colonia'];
       $perfilpersonal->cp = $_POST['cp'];
       $perfilpersonal->save();
    } else {
        
    }
})->name('nuevo-datospersonales-post');

$app->post('/nuevo-datos-academicos/',function() use($app){
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'institucion' => 'required|alpha',
        'carrera'    => 'required|alpha',
        'forma' => 'required|alpha',
        'ingreso' => 'required',
        'egreso' => 'required|numeric',
        'estado' => 'alpha',
        'municipio' => 'required|numeric|max_len,4|min_len,1',
        'localidad' => 'numeric|max_len,4|min_len,1',
        
    );
    $filters = array(
        'institucion' => 'trim',
        'carrera'    => 'trim',
        'forma' => 'trim',
        'ingreso' => 'trim',
        'egreso' => 'trom',
        'estado' => 'trim',
        'municipio' => 'trim',
        'localidad' => 'trim',
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if($validated === true) {
        $perfilacademico = new Academico();
        $perfilacademico->institucion = $_POST['institucion'];
        $perfilacademico->carrera = $_POST['carrera'];
        $perfilacademico->ingreso = $_POST['ingreso'];
        $perfilacademico->egreso = $_POST['egreso'];
        $perfilacademico->titulacion = $_POST['forma'];
        $perfilacademico->ubicacion = $_POST['localidad'];
        $perfilacademico->save();
    } else {
        
    }
})->name('nuevo-datosacademicos-post');

$app->post('/nuevo-info-contacto/',function() use($app){
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'email' => 'required|alpha',
        'enterado' => 'required|alpha',
        'mantener' => 'required|alpha',
        'movil' => 'required|numeric',
        'fijo' => 'alpha',
    );
    $filters = array(
        'email' => 'trim',
        'enterado' => 'trim',
        'mantener' => 'trim',
        'movil' => 'trim',
        'fijo' => 'trim',
        );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if($validated === true) {
		$perfilinfo = new Contacto();
        $perfilinfo->email = $_POST['email'];
        $perfilinfo->movil = $_POST['movil'];
        $perfilinfo->fijo = $_POST['fijo'];
        $perfilinfo->contactar = $_POST['mantener'];
        $perfilinfo->forma = $_POST['enterado'];
		$perfilinfo->save();     
    } else {
        
    }
})->name('nuevo-infocontacto-post');

$app->post('/nuevo-experiencia-laboral/',function() use($app){
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'trabajo' => 'required|alpha',
        'anostrabajo' => 'required|alpha',
    );
    $filters = array(
        'explab' => 'trim|sanitize_string',
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if($validated === true) {
		$perfillaboral = new Laboral();
        $perfillaboral->trabajado = $_POST['trabajo'];        
        $perfillaboral->experiencia = $_POST['explab'];
        $perfillaboral->tiempo = $_POST['anostrabajo'];
        $perfillaboral->save();
    } else {
        
    }
})->name('nuevo-explaboral-post');

$app->post('/nuevo-datos-docente/',function() use($app){
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'grado'    => 'required|alpha',
        'cedula'    => 'required|alpha_numeric',
        'especialidad'    => 'required|alpha',
        'promep'    => 'required',
        'sni'    => 'required',
        'completo'    => 'required'
    );
    $filters = array(
        'grado'    => 'trim',
        'cedula'    => 'trim',
        'especialidad'    => 'trim',
        'promep'    => 'trim',
        'sni'    => 'trim',
        'completo'    => 'trim'
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if($validated === true) {
		$perfldocente= new Docente();
		$perfldocente->grado = $_POST['grado'];
		$perfldocente->sni = $_POST['sni'];
		if(isset($_POST['pertenecesni'])) {
			$perfldocente->tiene_sni = $_POST['1'];
       } else {
			$perfldocente->tiene_sni = $_POST['0'];
       }
       
       $perfldocente->sni = $_POST['sni'];
       $perfldocente->especialidad = $_POST['especialidad'];
       $perfldocente->cedula = $_POST['cedula'];
       $perfldocente->promep = $_POST['promep'];
       $perfldocente->save();
    } else {
        
    }
})->name('nuevo-datosdocente-post');

/* =======================
 * ====== CATALOGOS ======
 * =======================*/ 
$app->get('/admin/catalogos/areas-interes/', function () use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Catálogos", "alias" => "admin-catalogos"),
		array("name" => "Areas de Interes", "alias" => "CatAreaInteres")
	);
	$data['areas_interes'] = AreaInteres::all();
    $app->render('areasinteres.html', $data);
})->name('CatAreaInteres');

$app->post('/nueva-area-interes/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === true) {
		$ainteres = new AreaInteres();
		$ainteres->nombre = $_POST['nombre'];
		$ainteres->save();

		$flash = array(
			"title" => "OK",
			"msg" => "Area de Interés se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);

		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatAreaInteres'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatAreaInteres'));
	}
})->name('nueva-ainteres-post');

$app->post('/actualiza-area-interes/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,100|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === true) {
		$ainteres = AreaInteres::find($id);
		$ainteres -> nombre = $_POST['nombre-edit'];
		$ainteres -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Datos del Área de Interés han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatAreaInteres'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatAreaInteres'));
	}
})->name('actualiza-ainteres-post');

$app->get('/borrar-area-interes/:id/', function($id) use($app){
	$relaciones = UsuariosAreas::find_all_by_area_id($id); 
	$cant_relaciones = count($relaciones);
	if ($cant_relaciones == 0){	
		$ainteres = AreaInteres::find($id);
		$ainteres->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "El Area de Interés ha sido borrada correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatAreaInteres'));
	} else {
		$flash = array(
			"title" => "OK",
			"msg" => "El Area de Interes esta relacionado, no se permite la eliminación.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatAreaInteres'));
	}
})->name('borrar-ainteres');

$app->get('/admin/catalogos/idiomas/', function () use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Catálogos", "alias" => "admin-catalogos"),
		array("name" => "Idiomas", "alias" => "CatIdiomas")
	);
	$data['idiomas'] = Idioma::all();
    $app->render('idiomas.html', $data);
})->name('CatIdiomas');

$app->post('/nuevo-idioma/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === true) {
		$idiomas = new Idioma();
		$idiomas->nombre = $_POST['nombre'];
		$idiomas->save();
		$flash = array(
			"title" => "OK",
			"msg" => "Idioma se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatIdiomas'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatIdiomas'));
	}
})->name('nuevo-idioma-post');

$app->post('/actualiza-idioma/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,100|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === true) {
		$idioma = Idioma::find($id);
		$idioma -> nombre = $_POST['nombre-edit'];
		$idioma -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Datos del Idioma han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatIdiomas'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatIdiomas'));
	}
})->name('actualiza-idioma-post');

$app->get('/borrar-idioma/:id/', function($id) use($app){
	$relaciones = UsuariosIdiomas::find_all_by_idioma_id($id); 
	$cant_relaciones = count($relaciones);
	if ($cant_relaciones == 0){	
		$ainteres = Idioma::find($id);		
		$ainteres->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "El idioma ha sido borrado correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatIdiomas'));
	} else {
		$flash = array(
			"title" => "OK",
			"msg" => "El Idioma esta relacionado, no se permite la eliminación.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatIdiomas'));
	}
})->name('borrar-idioma');

$app->get('/admin/catalogos/lenguajes/', function () use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Catálogos", "alias" => "admin-catalogos"),
		array("name" => "Lenguajes", "alias" => "CatLenguajes")
	);
	$data['lenguajes'] = Lenguaje::all();
    $app->render('lenguajes.html', $data);
})->name('CatLenguajes');

$app->post('/nuevo-lenguaje/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === true) {
		$ainteres = new Lenguaje();
		$ainteres->nombre = $_POST['nombre'];
		$ainteres->save();
		$flash = array(
			"title" => "OK",
			"msg" => "Lenguaje se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);
		$app-> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatLenguajes'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app -> flashKeep();
		$app -> redirect($app->urlFor('CatLenguajes'));
	}
})->name('nuevo-lenguaje-post');

$app->post('/actualiza-lenguaje/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,100|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === true) {
		$lenguaje = Lenguaje::find($id);
		$lenguaje -> nombre = $_POST['nombre-edit'];
		$lenguaje -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Datos del Lenguaje han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatLenguajes'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatLenguajes'));
	}
})->name('actualiza-lenguaje-post');

$app->get('/borrar-lenguaje/:id/', function($id) use($app){
	$relaciones = UsuariosLenguajes::find_all_by_lenguaje_id($id); 
	$cant_relaciones = count($relaciones);
	if ($cant_relaciones == 0){
		$ainteres = Lenguaje::find($id);
		$ainteres->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "El Lenguaje ha sido borrada correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatLenguajes'));
	} else {
		$flash = array(
			"title" => "OK",
			"msg" => "El Lenguaje esta relacionado, no se permite la eliminación.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatLenguajes'));
	}
})->name('borrar-lenguaje');

$app->get('/admin/catalogos/plataformas/', function () use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Catálogos", "alias" => "admin-catalogos"),
		array("name" => "Plataformas", "alias" => "CatPlataformas")
	);
	$data['plataformas'] = Plataforma::all();
    $app->render('plataformas.html', $data);
})->name('CatPlataformas');

$app->post('/nueva-plataforma/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === true) {
		$ainteres = new Plataforma();
		$ainteres->nombre = $_POST['nombre'];
		$ainteres->save();
		$flash = array(
			"title" => "OK",
			"msg" => "La Plataforma se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatPlataformas'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatPlataformas'));
	}
})->name('nueva-plataforma-post');

$app->post('/actualiza-plataforma/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === true) {
		$plataforma = Plataforma::find($id);
		$plataforma -> nombre = $_POST['nombre-edit'];
		$plataforma -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Datos de la Plataforma han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatPlataformas'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatPlataformas'));
	}
})->name('actualiza-plataforma-post');

$app->get('/borrar-plataforma/:id/', function($id) use($app){
	$relaciones = UsuariosPlataformas::find_all_by_plataforma_id($id); 
	$cant_relaciones = count($relaciones);
	if ($cant_relaciones == 0){		
		$ainteres = Plataforma::find($id);
		$ainteres->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "La Plataforma ha sido borrada correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatPlataformas'));
	} else {
		$flash = array(
			"title" => "OK",
			"msg" => "La Plataforma esta relacionado, no se permite la eliminación.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatPlataformas'));
	}
})->name('borrar-plataforma');

//*******
// Formas enterado
//******
$app->get('/admin/catalogos/formas-enterado/', function () use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Catálogos", "alias" => "admin-catalogos"),
		array("name" => "Formas de Enterado", "alias" => "CatFormasEnterado")
	);
	$data['forma_enterado'] = FormaEnterado::all();
    $app->render('formas_enterado.html', $data);
})->name('CatFormasEnterado');

$app->post('/nuevo-formasent/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$formaent = new FormaEnterado();
		$formaent->nombre = $_POST['nombre'];
		$formaent->save();
		$flash = array(
			"title" => "OK",
			"msg" => "Forma de Enterado se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatFormasEnterado'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatFormasEnterado'));
	}
})->name('nuevo-formaent-post');

$app->post('/actualiza-formasent/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$formaent = FormaEnterado::find($id);
		$formaent -> nombre = $_POST['nombre-edit'];
		$formaent -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Datos de la Forma de Enterado han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatFormasEnterado'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatFormasEnterado'));
	}
})->name('actualiza-formaent-post');

$app->get('/borrar-formaent/:id/', function($id) use($app){
		$formaent = FormaEnterado::find($id);		
		$formaent->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "Forma de Enterado ha sido borrado correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatFormasEnterado'));
})->name('borrar-formaent');


//*******
// Herramientas
//******
$app->get('/admin/catalogos/herramientas/', function () use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Catálogos", "alias" => "admin-catalogos"),
		array("name" => "Herramientas", "alias" => "CatHerramienta")
	);
	$data['herramientas'] = Herramienta::all();
    $app->render('herramientas.html', $data);
})->name('CatHerramienta');

$app->post('/nueva-herramienta/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$herramienta = new Herramienta();
		$herramienta->nombre = $_POST['nombre'];
		$herramienta->save();
		$flash = array(
			"title" => "OK",
			"msg" => "La Herramienta se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatHerramienta'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatHerramienta'));
	}
})->name('nueva-herramienta-post');

$app->post('/actualiza-herramienta/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$herramienta = Herramienta::find($id);
		$herramienta -> nombre = $_POST['nombre-edit'];
		$herramienta -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Datos de la Herramienta han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatHerramienta'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatHerramienta'));
	}
})->name('actualiza-herramienta-post');

$app->get('/borrar-herramienta/:id/', function($id) use($app){
	$relaciones = UsuariosHerramientas::find_all_by_herramienta_id($id); 
	$cant_relaciones = count($relaciones);
	if ($cant_relaciones == 0){		
		$herramienta = Herramienta::find($id);
		$herramienta->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "La Herramienta ha sido borrada correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatHerramienta'));
	}
	else {
		$flash = array(
			"title" => "OK",
			"msg" => "La Herramienta esta relacionado, no se permite la eliminación.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatHerramienta'));
	}
})->name('borrar-herramienta');

//*************************
//**** LINEAS DE INVESTIGACION
//*************************

$app->get('/admin/catalogos/lineas-investigacion/', function () use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Catálogos", "alias" => "admin-catalogos"),
		array("name" => "Líneas de Investigación", "alias" => "CatLineaInv")
	);
	$data['lineas_investigacion'] = LineaInvestigacion::all();
    $app->render('lineas_investigacion.html', $data);
})->name('CatLineaInv');

$app->post('/nueva-lineainv/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,100|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$lineainv = new LineaInvestigacion();
		$lineainv->nombre = $_POST['nombre'];
		$lineainv->save();
		$flash = array(
			"title" => "OK",
			"msg" => "La línea de investigación se agregó satisfactoriamente.",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app-> flashKeep();
		$app-> redirect($app->urlFor('CatLineaInv'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatLineaInv'));
	}
})->name('nueva-lineainv-post');

$app->post('/actualiza-lineainv/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,100|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$lineainv = LineaInvestigacion::find($id);
		$lineainv -> nombre = $_POST['nombre-edit'];
		$lineainv -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Los datos de la línea de investigación han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatLineaInv'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatLineaInv'));
	}
})->name('actualiza-lineainv-post');

$app->get('/borrar-lineainv/:id/', function($id) use($app){
	$relaciones = Materias::find_all_by_linea_investigacion($id); 
	$cant_relaciones = count($relaciones);
	if ($cant_relaciones == 0){	
		$lineainv = LineaInvestigacion::find($id);
		$lineainv->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "La línea de investigación ha sido borrada correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatLineaInv'));
	}
	else {
		$flash = array(
			"title" => "OK",
			"msg" => "La línea de investigación esta relacionada, no se permite la eliminación.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatInstitucion'));
	}
})->name('borrar-lineainv');


//*******
// Instituciones
//******
$app->get('/admin/catalogos/instituciones/', function () use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Catálogos", "alias" => "admin-catalogos"),
		array("name" => "Instituciones", "alias" => "CatInstitucion")
	);
	$data['instituciones'] = Institucion::all();
    $app->render('instituciones.html', $data);
})->name('CatInstitucion');

$app->post('/nueva-institucion/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,100|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$institucion = new Institucion();
		$institucion->nombre = $_POST['nombre'];
		$institucion->abreviatura = $_POST['abrev'];
		$institucion->save();
		$flash = array(
			"title" => "OK",
			"msg" => "La institución se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatInstitucion'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatInstitucion'));
	}
})->name('nueva-institucion-post');

$app->post('/actualiza-institucion/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,100|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$institucion = Institucion::find($id);
		$institucion -> nombre = $_POST['nombre-edit'];
		$institucion -> abreviatura = $_POST['abrev-edit'];
		$institucion -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Los datos de la institución han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatInstitucion'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatInstitucion'));
	}
})->name('actualiza-institucion-post');

$app->get('/borrar-institucion/:id/', function($id) use($app){
	$relaciones = Academico::find_all_by_institucion($id); 
	$cant_relaciones = count($relaciones);
	if ($cant_relaciones == 0){		
		$herramienta = Institucion::find($id);
		$herramienta->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "La institución ha sido borrada correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatInstitucion'));
	}
	else {
		$flash = array(
			"title" => "OK",
			"msg" => "La institución esta relacionada, no se permite la eliminación.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatInstitucion'));
	}
})->name('borrar-institucion');

//*******
// Roles
//******
$app->get('/admin/roles/', function () use($app) {
	$data['roles'] = Rol::all();
    $app->render('roles.html', $data);
})->name('CatRol');

$app->post('/nuevo-rol/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,100|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$rol = new Rol();
		$rol->nombre = $_POST['nombre'];
		$rol->save();
		$flash = array(
			"title" => "OK",
			"msg" => "El Rol se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatRol'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatRol'));
	}
})->name('nuevo-rol-post');

$app->post('/actualiza-rol/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,100|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$rol = Rol::find($id);
		$rol -> nombre = $_POST['nombre-edit'];
		$rol -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Datos del rol han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatRol'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatRol'));
	}
})->name('actualiza-rol-post');

$app->get('/borrar-rol/:id/', function($id) use($app){
	$relaciones = UsuariosRoles::find_all_by_rol_id($id); 
	$cant_relaciones = count($relaciones);
	if ($cant_relaciones == 0){		
		$herramienta = Rol::find($id);
		$herramienta->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "El Rol ha sido borrada correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatRol'));
	}
	else {
		$flash = array(
			"title" => "OK",
			"msg" => "El Rol esta relacionado, no se permite la eliminación.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatRol'));
	}
})->name('borrar-rol');

//****************************
//*******
// Eventos
//******
$app->get('/catalogos/eventos/', function () use($app) {
	$data['eventos'] = Evento::all();
    $app->render('nuevoevento.html', $data);
})->name('CatEvento');

$app->post('/nuevo-evento/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,100|min_len,1',
		'autor'    => 'required|max_len,50|min_len,1',
		'descripcion'    => 'required|max_len,100|min_len,1',
		'fecha_inicio'    => 'required|max_len,100|min_len,1',
		'fecha_fin'    => 'required|max_len,100|min_len,1',
		'prioridad'    => 'required|max_len,100|min_len,1',
		'fecha_creado'    => 'required|max_len,10|min_len,1',
		'hora_inicio'    => 'required|max_len,10|min_len,1',
		'hora_fin'    => 'required|max_len,100|min_len,1',
		'validado'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$evento = new Evento();
		$evento->nombre = $_POST['nombre'];
		$evento->autor = $_POST['autor'];
		$evento->descripcion = $_POST['descripcion'];
		$evento->fecha_inicio = $_POST['fecha_inicio'];
		$evento->fecha_fin = $_POST['fecha_fin'];
		$evento->prioridad = $_POST['prioridad'];
		$evento->fecha_creado = $_POST['fecha_creado'];
		$evento->hora_inicio = $_POST['hora_inicio'];
		$evento->hora_fin = $_POST['hora_fin'];
		$evento->validado = $_POST['validado'];
		$evento->save();
		$flash = array(
			"title" => "OK",
			"msg" => "El evento se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatEvento'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatEvento'));
	}
})->name('nuevo-evento-post');

$app->post('/actualiza-evento/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,100|min_len,1',
		'autor-edit'    => 'required|max_len,50|min_len,1',
		'descripcion-edit'    => 'required|max_len,100|min_len,1',
		'fecha_inicio-edit'    => 'required|max_len,100|min_len,1',
		'fecha_fin-edit'    => 'required|max_len,100|min_len,1',
		'prioridad-edit'    => 'required|max_len,100|min_len,1',
		'fecha_creado-edit'    => 'required|max_len,10|min_len,1',
		'hora_inicio-edit'    => 'required|max_len,10|min_len,1',
		'hora_fin-edit'    => 'required|max_len,100|min_len,1',
		'validado-edit'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$evento = Evento::find($id);
		$evento->nombre = $_POST['nombre-edit'];
		$evento->autor = $_POST['autor-edit'];
		$evento->descripcion = $_POST['descripcion-edit'];
		$evento->fecha_inicio = $_POST['fecha_inicio-edit'];
		$evento->fecha_fin = $_POST['fecha_fin-edit'];
		$evento->prioridad = $_POST['prioridad-edit'];
		$evento->fecha_creado = $_POST['fecha_creado-edit'];
		$evento->hora_inicio = $_POST['hora_inicio-edit'];
		$evento->hora_fin = $_POST['hora_fin-edit'];
		$evento->validado = $_POST['validado-edit'];
		$evento -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Datos del evento han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatEvento'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatEvento'));
	}
})->name('actualiza-evento-post');

$app->get('/borrar-evento/:id/', function($id) use($app){

	$relaciones = UsuariosEventos::find_all_by_evento_id($id); 
	$cant_relaciones = count($relaciones);
	if ($cant_relaciones == 0){
		
		$herramienta = Evento::find($id);
		$herramienta->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "El evento ha sido borrada correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatEvento'));

	}
	else {
		$flash = array(
			"title" => "OK",
			"msg" => "El evento esta relacionado, no se permite la eliminación.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatEvento'));
	}
 
})->name('borrar-evento');




//*******
// MATERIAS
//******
$app->get('/admin/catalogos/materias/', function () use($app) {
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Catálogos", "alias" => "admin-catalogos"),
		array("name" => "Areas de Interes", "alias" => "CatMateria")
	);
	$data['materias'] = Materia::all();
	$data['lineas_investigacion'] = LineaInvestigacion::find('all', array('order' => 'nombre asc'));
    $app->render('materias.html', $data);
})->name('CatMateria');

$app->post('/nueva-materia/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,100|min_len,1',
		'semestre'    => 'required|numeric|max_len,2|min_len,1',
		'linea_investigacion'    => 'required|max_len,100|min_len,1',
		'doc'    => 'required|max_len,100|min_len,1',
		'tis'    => 'required|max_len,100|min_len,1',
		'tps'    => 'required|max_len,100|min_len,1',
		'horas_totales'    => 'required|numeric|max_len,4|min_len,1',
		'creditos'    => 'required|numeric|max_len,3|min_len,1',
		'link_pdf'    => 'required|max_len,100|min_len,1',
		'tipo'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$materia = new Materia();
		$materia->nombre = $_POST['nombre'];
		$materia->semestre = $_POST['semestre'];
		$materia->linea_investigacion = $_POST['linea_investigacion'];
		$materia->doc = $_POST['doc'];
		$materia->tis = $_POST['tis'];
		$materia->tps = $_POST['tps'];
		$materia->horas_totales = $_POST['horas_totales'];
		$materia->creditos = $_POST['creditos'];
		$materia->link_pdf = $_POST['link_pdf'];
		$materia->tipo = $_POST['tipo'];
		$materia->save();
		$flash = array(
			"title" => "OK",
			"msg" => "La Materia se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatMateria'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatMateria'));
	}
})->name('nueva-materia-post');

$app->post('/actualiza-materia/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,100|min_len,1',
		'semestre-edit'    => 'required|numeric|max_len,2|min_len,1',
		'linea_investigacion-edit'    => 'required|max_len,100|min_len,1',
		'doc-edit'    => 'required|max_len,100|min_len,1',
		'tis-edit'    => 'required|max_len,100|min_len,1',
		'tps-edit'    => 'required|max_len,100|min_len,1',
		'horas_totales-edit'    => 'required|numeric|max_len,4|min_len,1',
		'creditos-edit'    => 'required|numeric|max_len,3|min_len,1',
		'link_pdf-edit'    => 'required|max_len,100|min_len,1',
		'tipo-edit'    => 'required|max_len,10|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$materia = Materia::find($id);
		$materia -> nombre = $_POST['nombre-edit'];
		$materia -> semestre = $_POST['semestre-edit'];
		$materia -> linea_investigacion = $_POST['linea_investigacion-edit'];
		$materia -> doc = $_POST['doc-edit'];
		$materia -> tis = $_POST['tis-edit'];
		$materia -> tps = $_POST['tps-edit'];
		$materia -> horas_totales = $_POST['horas_totales-edit'];
		$materia -> creditos = $_POST['creditos-edit'];
		$materia -> link_pdf = $_POST['link_pdf-edit'];
		$materia -> tipo = $_POST['tipo-edit'];
		$materia -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Datos de la Materia han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatMateria'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatMateria'));
	}
})->name('actualiza-materia-post');

$app->get('/borrar-materia/:id/', function($id) use($app){
/*	
	$relaciones = Academico::find_all_by_institucion($id); 
	$cant_relaciones = count($relaciones);
	if ($cant_relaciones == 0){
  */		
		$herramienta = Materia::find($id);
		$herramienta->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "La Materia ha sido borrada correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatMateria'));
/*
	}
	else {
		$flash = array(
			"title" => "OK",
			"msg" => "La materia esta relacionado, no se permite la eliminación.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatMateria'));
	}
 */
})->name('borrar-materia');

//*******
// Carreras
//******
$app->get('/catalogos/carreras/', function () use($app) {
	$data['carreras'] = Carrera::all();
    $app->render('carreras.html', $data);
})->name('CatCarrera');


$app->post('/nueva-carrera/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$carrera = new Carrera();
		$carrera->nombre = $_POST['nombre'];
		$carrera->save();
		$flash = array(
			"title" => "OK",
			"msg" => "La Carrera se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatCarrera'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatCarrera'));
	}
})->name('nueva-carrera-post');

$app->post('/actualiza-carrera/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$carrera = Carrera::find($id);
		$carrera -> nombre = $_POST['nombre-edit'];
		$carrera -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Datos de la Carrera han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatCarrera'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatCarrera'));
	}
})->name('actualiza-carrera-post');

//
$app->get('/borrar-carrera/:id/', function($id) use($app){
//	$relaciones = UsuariosHerramientas::find_all_by_herramienta_id($id); 
//	$cant_relaciones = count($relaciones);
//	if ($cant_relaciones == 0){		
		$carrera = Carrera::find($id);
		$carrera->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "La Carrera ha sido borrada correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatCarrera'));
//	}
//	else {
//		$flash = array(
//			"title" => "OK",
//			"msg" => "La Carrera esta relacionado, no se permite la eliminación.",
//			"type" => "info",
//			"fade" => 1
//		);
//		$app -> flash("flash", $flash);
//		$app->flashKeep();
//		$app->redirect($app->urlFor('CatHerramienta'));
//	}
})->name('borrar-carrera');


//*******
// Formas de Titulacion
//******
$app->get('/catalogos/formastitulacion/', function () use($app) {
	$data['formas_titulacion'] = Formas_titulacion::all();
    $app->render('formas_titulacion.html', $data);
})->name('CatFormaTitulacion');

$app->post('/nueva-titulacion/', function() use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$titulacion = new Formas_titulacion();
		$titulacion->nombre = $_POST['nombre'];
		$titulacion->save();
		$flash = array(
			"title" => "OK",
			"msg" => "La Forma de Titulación se agregó satisfactoriamente .",
			"type" => "success",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatFormaTitulacion'));
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatFormaTitulacion'));
	}
})->name('nueva-titulacion-post');

$app->post('/actualiza-titulacion/:id/', function($id) use($app){
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'nombre-edit'    => 'required|max_len,50|min_len,1',
	);
	$filters = array(
		'nombre-edit' 	  => 'trim|sanitize_string',
	);
	$post = $_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$titulacion = Formas_titulacion::find($id);
		$titulacion -> nombre = $_POST['nombre-edit'];
		$titulacion -> save();
		$flash = array(
			"title" => "OK",
			"msg" => "Datos de la Forma de Titulación han sido actualizados correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatFormaTitulacion'));		
	} else {
		$msgs = humanize_gump($validated);
		$flash = array(
			"title" => "ERROR",
			"msg" => $msgs,
			"type" => "error",
			"fade" => 0
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatFormaTitulacion'));
	}
})->name('actualiza-titulacion-post');

//
$app->get('/borrar-titulacion/:id/', function($id) use($app){
//	$relaciones = UsuariosHerramientas::find_all_by_herramienta_id($id); 
//	$cant_relaciones = count($relaciones);
//	if ($cant_relaciones == 0){		
		$carrera = Formas_titulacion::find($id);
		$carrera->delete();
		$flash = array(
			"title" => "OK",
			"msg" => "La Forma de Titulación ha sido borrada correctamente.",
			"type" => "info",
			"fade" => 1
		);
		$app -> flash("flash", $flash);
		$app->flashKeep();
		$app->redirect($app->urlFor('CatCarrera'));
//	}
//	else {
//		$flash = array(
//			"title" => "OK",
//			"msg" => "La Carrera esta relacionado, no se permite la eliminación.",
//			"type" => "info",
//			"fade" => 1
//		);
//		$app -> flash("flash", $flash);
//		$app->flashKeep();
//		$app->redirect($app->urlFor('CatHerramienta'));
//	}
})->name('borrar-titulacion');

/* =======================
 * ====== PRINCIPAL ======
 * =======================*/
$app->get('/(:slug/)', function ($slug = "") use($app) {
	$data['user'] = isAllowed("Administrador", false);
	if($slug != ""){
		$seccion = Seccion::find_by_slug($slug);
		if($seccion){
			$seccion->contenido = replace_hashes($seccion->contenido);
			$data['seccion'] = $seccion;
		}
	}
    $app->render('index.html', $data);
})->name('home');

require 'functions.inc.php'; 

$app->run();

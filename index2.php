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

Ladybug\Autoloader::register();

$connections = array(
    'dev' => 'mysql://root:root@localhost/posgrado',
    'prod' => 'mysql://'.getenv('MYSQL_USERNAME').':'.getenv('MYSQL_PASSWORD').'@'.getenv('MYSQL_DB_HOST').'/'.getenv('MYSQL_DB_NAME').';charset=utf8'
);
ActiveRecord\Config::initialize(function($cfg) use ($connections) {
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
/*$app->get('/login/', function () use ($app) {
    $app->render('login.html');
})->name('login');*/

$app->post('/login/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'usuario'    => 'required|alpha_numeric|max_len,100|min_len,5',
        'password'    => 'required|max_len,100|min_len,6',
    );

    $filters = array(
        'usuario' 	  => 'trim|sanitize_string',
        'password'	  => 'trim|md5'
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === TRUE) {
        $user = Usuario::find_by_usuario($_POST['usuario']);
        if (is_object($user)) {
            if ($user->password === $_POST['password']) {
                $expiration = (isset($_POST['cookie']) ? '1 month' : '2 hours');
                $app -> setCookie('userId', $user -> id, $expiration);
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
$app->get('/admin/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin")
    );
    $data['user'] = isAllowed("Administrador",FALSE);
    $app->render('dashboard.html',$data);
})->name('admin');

$app->get('/admin/usuarios/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Gestor de Usuarios", "alias" => "admin-usuarios")
    );
    $data['user'] = isAllowed("Administrador",FALSE);
    $data['usuarios'] = Usuario::find('all', array('order'=>'activo desc','include' => array('personal','ur','contacto')));
    $data['roles'] = Rol::find('all', array('order' => 'nombre asc'));
    $app->render('users.html', $data);
})->name('admin-usuarios');

$app->post('/nuevo-usuario/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'usuario'    => 'required|alpha_dash|max_len,100|min_len,4',
        'email'    => 'required|valid_email',
    );

    $filters = array(
        'usuario' 	  => 'trim|sanitize_string',
        'email'	  => 'trim|sanitize_email'
    );
    $post = $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === TRUE) {
        $tb = new Toolbox();
        $password = $tb->generate_password(7,7);
        $ok = Usuario::transaction(function() use ($post,$tb,$password) {
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
        if ($ok) {
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
            if ($mailed) {
                $msg = "El usuario se ha creado con éxito, y se ha enviado notificación al correo indicado.";
            } else {
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

$app->post('/actualiza-usuario/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $password = $_POST['password'];
    $rules = array(
        'usuario-edit'    => 'required|alpha_dash|max_len,100|min_len,4',
        'email-edit'    => 'required|valid_email',
    );

    $filters = array(
        'usuario-edit' 	  => 'trim|sanitize_string',
        'email-edit'	  => 'trim|sanitize_email',
        'password' => 'trim|md5'
    );
    $post = $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === TRUE) {
        $ok = Usuario::transaction(function() use ($post,$password,$id) {
            $usuario = Usuario::find($id);
            if ($_POST['usuario-edit'] != "") {
                $usuario->usuario = $_POST['usuario-edit'];
            }
            if ($password != "") {
                $usuario->password = md5($password);
            }
            $usuario->actualizado = time();
            $usuario->save();
            if(!$usuario)return false;

            if ($_POST['rol-edit'] != 0) {
                $ur = UsuariosRoles::find_by_usuario_id($id);
                $ur->rol_id = $_POST['rol-edit'];
                $ur->save();
                if(!$ur)return false;
            }

            if ($_POST['email-edit'] != "") {
                $contacto = Contacto::find_by_usuario_id($id);
                $contacto->email = $_POST['email-edit'];
                $contacto->save();
                if(!$contacto)return false;
            }

            return true;
        });
        if ($ok) {
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

$app->get('/cambiar-status-usuario/:id/', function($id) use ($app) {
    $usuario = Usuario::find($id);
    if ($usuario->activo == 0) {
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

$app->get('/admin/roles/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Gestor de Roles", "alias" => "admin-roles")
    );
    $data['user'] = isAllowed("Administrador",FALSE);
})->name('admin-roles');

#NOT USED
$app->get('/admin/permisos/', function() use ($app) {
    $data['user'] = isAllowed("Administrador",FALSE);
})->name('admin-permisos');

$app->get('/admin/aspirantes/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Gestor de Aspirantes", "alias" => "admin-aspirantes")
    );
    $data['user'] = isAllowed("Administrador",FALSE);
    $rolAspirante = Rol::find_by_nombre("Aspirante");
    $idAspirantes = UsuariosRoles::find_all_by_rol_id($rolAspirante->id);
    $ids = array();
    foreach ($idAspirantes as $ia) { $ids[] = $ia->usuario_id; }
    $data['aspirantes'] = "";
    if (!empty($ids)) {
        $data['aspirantes'] = Usuario::find_all_by_id($ids, array('order'=>'creado asc','include' => array('personal','ur','contacto')));
    }
    $app->render('aspirantes.html', $data);
})->name('admin-aspirantes');

$app->get('/procesar-aspirante/:action/:id/', function($action,$id) use ($app) {
    $rolAlumno = Rol::find_by_nombre("Alumno");
    $rolNoAceptado = Rol::find_by_nombre("No aceptado");
    $ur = UsuariosRoles::find_by_usuario_id($id);

    if ($action == "aceptar") {
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

$app->get('/admin/archivos/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Gestor de Archivos", "alias" => "admin-archivos")
    );
    $data['user'] = isAllowed("Administrador",FALSE);
    $app->render('filemanager.html', $data);
})->name('admin-archivos');

$app->map("/admin/elFinder/", function() use ($app) {
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

$app->get('/explorador-archivos/', function() use ($app) {
    $data['user'] = isAllowed("Verificador",FALSE);
    $dir = ($dir == "/Drive/") ? $dir : urldecode($dir);
    $dir = "/Drive/";
    $dir = __DIR__.$dir;
    $lister = new DirectoryLister();
    $data['directory'] = $lister->listDirectory($dir);
    $data['breadcrumbs'] = $lister->listBreadcrumbs();
    $app->render('explorer.html', $data);
})->name('explorador-archivos');

$app->post('/dirlist/', function() use ($app) {
    $root = __DIR__;
    $_POST['dir'] = urldecode($_POST['dir']);
    if ( file_exists($root . $_POST['dir']) ) {
        $files = scandir($root . $_POST['dir']);
        natcasesort($files);
        $tb = new Toolbox();
        if ( count($files) > 2 ) { /* The 2 accounts for . and .. */
            echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
            // All dirs
            foreach ($files as $file) {
                $begin = $file[0];
                if ( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file)) {
                    if ($begin != ".") {
                        $realPath = $root.$_POST['dir'].$file;
                        echo "<li class=\"directory collapsed\">";
                        echo "<a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">";
                        echo htmlentities($file) . "</a></li>";
                    }
                }
            }
            // All files
            foreach ($files as $file) {
                if ( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
                    $ext = preg_replace('/^.*\./', '', $file);
                    $begin = $file[0];
                    if ($begin != ".") {
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

$app->get('/download/:file', function($file) use ($app) {
    $file = base64_decode($file);
    $file = __DIR__."/".$file;
    if (!file_exists($file)) {
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

$app->post('/uploader/',function() use ($app) {
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

$app->get('/admin/secciones/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Gestor de Secciones", "alias" => "admin-secciones")
    );
    $data['user'] = isAllowed("Administrador",FALSE);
    $data['secciones'] = $sections = Seccion::find('all', array('order' => 'nombre asc'));
/*	$baseSections = array('Antecedentes','Misión y Visión','Objetivos','Logros y Reconocimientos',
    'Líneas de Generación y Aplicación de Conocimiento','Vinculación','Proceso de Admisión',
    'Perfil de Ingreso','Perfil de Egreso','Curso Propedéutico','Material de Apoyo',
    'Requisitos para Obtención de Grado','Líneas de Investigación');
    $secs = array();
    foreach ($sections as $s) {
        $secs[] = $s->nombre;
    }
    if (count($baseSections) != count($secs)) {
        $tb = new Toolbox();
        foreach ($baseSections as $bs) {
            if (!in_array($bs,$secs)) {
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

$app->get('/admin/secciones/editor/:slug', function($slug) use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Gestor de Secciones", "alias" => "admin-secciones"),
        array("name" => "Editor", "alias" => "editor-seccion")
    );
    $data['secciones'] = Seccion::find('all', array('order' => 'nombre asc'));
    $data['seccion'] = Seccion::find_by_slug($slug);

    $app->render('editor-seccion.html', $data);
})->name('editor-seccion');

$app->post('/actualiza-seccion/:id/', function($id) use ($app) {
    $validator = new GUMP();
    //$_POST = $validator->sanitize($_POST);
    $rules = array(
    );

    $filters = array(
    );
    $post = $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === TRUE) {
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

$app->get('/admin/noticias/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Gestor de Noticias", "alias" => "admin-noticias")
    );
    $data['user'] = isAllowed("Administrador",FALSE);
})->name('admin-noticias');

$app->get('/admin/catalogos/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Catálogos", "alias" => "admin-catalogos")
    );
    $data['user'] = isAllowed("Administrador",FALSE);
    $app->render('catalogos.html', $data);
})->name('admin-catalogos');

/* =======================
 * ======= PUBLICO =======
 * =======================*/
$app->get('/estructura-plan-estudios/', function() use ($app) {
    $data['optativas'] = Materia::find_by_tipo(0);
    $data['tesis'] = Materia::find_by_nombre('Tesis');
    $data['bd'] = Materia::find_by_nombre('Base de Datos');
    $data['is'] = Materia::find_by_nombre('Ingenieria de Software');
    $data['teoria'] = Materia::find_by_nombre('Teoria de la Computacion');
    $data['modelado'] = Materia::find_by_nombre('Modelado Conceptual de Aplicaciones Web');
    $data['seminario'] = Materia::find_by_nombre('seminario');
    $app->render('reticula02.html',$data);
})->name('plan-estudios');

$app->get('/nucleo-academico/', function() use ($app) {
    $data['rol'] = Rol::find_by_nombre('Docente');
    $rolus = UsuariosRoles::find_by_rol_id($data['rol']->id);
    $data['usuarios'] = Usuario::find_all_by_id($rolus->usuario_id,array('include'=> array('personal','docente')));
    $app->render('nucleoacademico.html',$data);
})->name('nucleo-academico');

$app->get('/productividad-academica/', function() use ($app) {
    $data['rol'] = Rol::find_by_nombre('Docente');
    $rolus = UsuariosRoles::find_all_by_rol_id($data['rol']->id);
    $idusuarios = array();
    foreach ($rolus as $u) {
        $idusuarios[] = $u->id;
    }
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
    #$app->render('productividadacademica.html',$data);
})->name('productividad-academica');

$app->get('/calendario/', function() use ($app) {
})->name('calendario');

$app->get('/relacion-aceptados/', function() use ($app) {
})->name('relacion-aceptados');

$app->get('/horarios/', function() use ($app) {
})->name('horarios');

$app->get('/publicaciones/', function() use ($app) {
    $user['usuarios'] = Usuario::find_by_id('1');
    $data['publicaciones']= Publicacion::find_all_by_autor($user['usuarios']->id);
    $app->render('publicaciones.html',$data);
})->name('publicaciones');

$app->get('/egresados/', function() use ($app) {
})->name('egresados');

$app->get('/matriculacion/', function() use ($app) {
    $app->render('EstadisticaMatriculacion.html');
})->name('matriculacion');

/* =======================
 * ======= DOCENTE =======
 * =======================*/

$app->get('/docente/', function() use ($app) {
     $app->render('docente.html');
})->name('docente');

$app->get('/docente/tesistas/', function() use ($app) {
    $data['user'] = isAllowed("Docente",FALSE);
    $app->render('gestTesista02.html');
})->name('docente-tesistas');

$app->get('/docente/eventos/', function() use ($app) {
    $user ['usuarios']= Usuario::find_by_id('1');
    $data['eventos']= Evento::find_all_by_autor($user['usuarios']->id);
    $app->render('eventosDoc.html',$data);
})->name('eventosDoc');

$app->post('/nuevo-tesista/',function() use ($app) {
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
    if ($validated === TRUE) {

    } else {

    }
})->name('nuevo-tesista-post');

$app->get('/docente/perfil/', function() use ($app) {
    $data['user'] = isAllowed("Docente",FALSE);
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

$app->post('/nuevo-subir-doc/',function() use ($app) {
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
    if ($validated === TRUE) {

    } else {

    }
})->name('nuevo-subir-doc-post');

/* =======================
 * === ALUMNO/ASPIRANTE ==
 * =======================*/
$app->get('/editor-perfil/', function() use ($app) {
    $data['user'] = isAllowed(array('Alumno','Aspirante'),FALSE);
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

$app->post('/nuevo-datos-personales/',function() use ($app) {
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
    if ($validated === TRUE) {
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

$app->post('/nuevo-datos-academicos/',function() use ($app) {
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
    if ($validated === TRUE) {
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

$app->post('/nuevo-info-contacto/',function() use ($app) {
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
    if ($validated === TRUE) {
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

$app->post('/nuevo-experiencia-laboral/',function() use ($app) {
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
    if ($validated === TRUE) {
        $perfillaboral = new Laboral();
        $perfillaboral->trabajado = $_POST['trabajo'];
        $perfillaboral->experiencia = $_POST['explab'];
        $perfillaboral->tiempo = $_POST['anostrabajo'];
        $perfillaboral->save();
    } else {

    }
})->name('nuevo-explaboral-post');

$app->post('/nuevo-datos-docente/',function() use ($app) {
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
    if ($validated === TRUE) {
        $perfldocente= new Docente();
        $perfldocente->grado = $_POST['grado'];
        $perfldocente->sni = $_POST['sni'];
        if (isset($_POST['pertenecesni'])) {
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
$app->get('/catalogos/areas-interes/', function () use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Catálogos", "alias" => "admin-catalogos"),
        array("name" => "Areas de Interes", "alias" => "CatAreaInteres")
    );
    $data['areas_interes'] = AreaInteres::all();
    $app->render('areasinteres.html', $data);
})->name('CatAreaInteres');

$app->post('/nueva-area-interes/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'nombre'    => 'required|alpha_dash|max_len,100|min_len,4',
    );
    $filters = array(
        'nombre' 	  => 'trim|sanitize_string',
    );
    $post = $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === TRUE) {
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

$app->post('/actualiza-area-interes/:id/', function($id) use ($app) {
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
    if ($validated === TRUE) {
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

$app->get('/borrar-area-interes/:id/', function($id) use ($app) {
    $relaciones = UsuariosAreas::find_all_by_area_id($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
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

$app->get('/catalogos/idiomas/', function () use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Catálogos", "alias" => "admin-catalogos"),
        array("name" => "Idiomas", "alias" => "CatIdiomas")
    );
    $data['idiomas'] = Idioma::all();
    $app->render('idiomas.html', $data);
})->name('CatIdiomas');

$app->post('/nuevo-idioma/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'nombre'    => 'required|alpha_dash|max_len,100|min_len,4',
    );
    $filters = array(
        'nombre' 	  => 'trim|sanitize_string',
    );
    $post = $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === TRUE) {
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

$app->post('/actualiza-idioma/:id/', function($id) use ($app) {
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
    if ($validated === TRUE) {
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

$app->get('/borrar-idioma/:id/', function($id) use ($app) {
    $relaciones = UsuariosIdiomas::find_all_by_idioma_id($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
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

$app->get('/catalogos/lenguajes/', function () use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Catálogos", "alias" => "admin-catalogos"),
        array("name" => "Lenguajes", "alias" => "CatLenguajes")
    );
    $data['lenguajes'] = Lenguaje::all();
    $app->render('lenguajes.html', $data);
})->name('CatLenguajes');

$app->post('/nuevo-lenguaje/', function() use ($app) {
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
    if ($validated === TRUE) {
        $ainteres = new Lenguaje();
        $ainteres->nombre = $_POST['nombre'];
        $ainteres->save();
        $flash = array(
            "title" => "OK",
            "msg" => "Lenguaje se agregó satisfactoriamente .",
            "type" => "success",
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
})->name('nuevo-lenguaje-post');

$app->post('/actualiza-lenguaje/:id/', function($id) use ($app) {
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
    if ($validated === TRUE) {
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

$app->get('/borrar-lenguaje/:id/', function($id) use ($app) {
    $relaciones = UsuariosLenguajes::find_all_by_lenguaje_id($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
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

$app->get('/catalogos/plataformas/', function () use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Catálogos", "alias" => "admin-catalogos"),
        array("name" => "Plataformas", "alias" => "CatPlataformas")
    );
    $data['plataformas'] = Plataforma::all();
    $app->render('plataformas.html', $data);
})->name('CatPlataformas');

$app->post('/nueva-plataforma/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'nombre'    => 'required|alpha_dash|max_len,100|min_len,1',
    );
    $filters = array(
        'nombre' 	  => 'trim|sanitize_string',
    );
    $post = $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === TRUE) {
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

$app->post('/actualiza-plataforma/:id/', function($id) use ($app) {
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
    if ($validated === TRUE) {
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

$app->get('/borrar-plataforma/:id/', function($id) use ($app) {
    $relaciones = UsuariosPlataformas::find_all_by_plataforma_id($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
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

/* =======================
 * ====== PRINCIPAL ======
 * =======================*/
$app->get('/(:slug/)', function ($slug = "") use ($app) {
    $data['user'] = isAllowed("Administrador",FALSE);
    if ($slug != "") {
        $seccion = Seccion::find_by_slug($slug);
        if ($seccion) {
            $seccion->contenido = replace_hashes($seccion->contenido);
            $data['seccion'] = $seccion;
        }
    }
    $app->render('index.html', $data);
})->name('home');

require 'functions.inc.php';

$app->run();


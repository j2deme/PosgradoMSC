<?php
$app->get('/admin/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin")
    );
    $data['user'] = isAllowed("Administrador",false);
    $app->render('dashboard.html',$data);
})->name('admin');

$app->get('/admin/usuarios/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Gestor de Usuarios", "alias" => "admin-usuarios")
    );
    $data['user'] = isAllowed("Administrador",false);
    $data['usuarios'] = Usuario::find('all', array('order'=>'activo desc','include' => array('personal','ur','contacto')));
    $data['roles'] = Rol::find('all', array('order' => 'nombre asc'));
    $app->render('users.html', $data);
})->name('admin-usuarios');

$app->post('/nuevo-usuario/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'usuario'    => 'required|alpha_dash|min_len,2|max_len,100',
        'email'    => 'required|valid_email',
    );

    $filters = array(
        'usuario'     => 'trim|sanitize_string',
        'email'   => 'trim|sanitize_email'
    );
    $post = $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === true) {
        $tb = new Toolbox();
        $password = $tb->generate_password(7,7);
        $ok = Usuario::transaction(function() use ($post,$tb,$password) {
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
/*TODO Verificar recepcion de correo con datos de acceso
 Generar interfaz para activación de cuenta y cambio de contraseña*/
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

$app->post('/user-available/', function() use($app) {
    $nick = $_POST['username'];
    $nick = trim($nick);
    $usuario = Usuario::find_by_login($nick);
    $num = (is_object($usuario)) ? 1 : 0;
    echo $num;
})->name('user-available');

$app->post('/actualiza-usuario/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $password = $_POST['password'];
    $rules = array(
        'usuario-edit'    => 'required|alpha_dash|max_len,100',
        'email-edit'    => 'required|valid_email',
    );

    $filters = array(
        'usuario-edit'    => 'trim|sanitize_string',
        'email-edit'      => 'trim|sanitize_email',
        'password' => 'trim|md5'
    );
    $post = $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === true) {
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
/*TODO Enviar el correo al usuario con los datos de acceso actualizados*/
            $flash = array(
                "title" => "OK",
                "msg" => "Usuario Actualizado.",
                "type" => "success",
                "fade" => 1
            );
        } else {
            $flash = array(
                "title" => "ERROR",
                "msg" => "Ha sucedido un error inesperado, intenta nuevamente.",
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
        "msg" => "El usuario ha sido $verb.",
        "type" => "info",
        "fade" => 1
    );
    $app -> flash("flash", $flash);
    $app->flashKeep();
    $app->redirect($app->urlFor('admin-usuarios'));
})->name('cambiar-status-usuario');

#XXX NOT USED
$app->get('/admin/roles/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Roles", "alias" => "admin-roles")
    );
    $data['user'] = isAllowed("Administrador",false);
})->name('admin-roles');

$app->get('/admin/permisos/', function() use ($app) {
    $data['user'] = isAllowed("Administrador",false);
})->name('admin-permisos');
#XXX NOT USED

$app->get('/admin/aspirantes/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Aspirantes", "alias" => "admin-aspirantes")
    );
    $data['user'] = isAllowed("Administrador",false);
    $rolAspirante = Rol::find_by_nombre("Aspirante");
    $idAspirantes = UsuariosRoles::find_all_by_rol_id($rolAspirante->id);
    $ids = array();
    foreach ($idAspirantes as $ia) {
        $ids[] = $ia->usuario_id;
    }
    $data['aspirantes'] = array();
    if (!empty($ids)) {
        $data['aspirantes'] = Usuario::find_all_by_id($ids, array('order'=>'creado asc','include' => array('personal','ur','contacto')));
    }
    $app->render('aspirantes.html', $data);
})->name('admin-aspirantes');

#TODO Agregar interaccion para modificar estadistica de matriculacion y genero
$app->get('/procesar-aspirante/:action/:id/', function($action,$id) use ($app) {
    $rolAlumno = Rol::find_by_nombre("Alumno");
    $rolNoAceptado = Rol::find_by_nombre("No aceptado");
    $ur = UsuariosRoles::find_by_usuario_id($id);
    if ($action == "aceptar") {
        $ur->rol_id = $rolAlumno->id;
        $verb = "aceptado";
        $posgrado = Posgrado::find_by_usuario_id($id);
        if(is_object($posgrado)){
            $posgrado->generacion = date("Y");
            $posgrado->save();
        } else {
            $posgrado = new Posgrado();
            $posgrado->usuario_id = $id;
            $posgrado->generacion = date("Y");
            $posgrado->save();
        }
    } else {
        $user = Usuario::find($id);
        $user->intentos++;
        $user->save();
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
        array("name" => "Archivos", "alias" => "admin-archivos")
    );
    $data['user'] = isAllowed("Administrador",false);
    $app->render('filemanager.html', $data);
})->name('admin-archivos');

$app->get('/admin/secciones/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Secciones", "alias" => "admin-secciones")
    );
    $data['user'] = isAllowed("Administrador", false);
    $data['secciones'] = $sections = Seccion::find('all', array('order' => 'nombre asc'));
/*  $baseSections = array('Antecedentes','Misión y Visión','Objetivos','Logros y Reconocimientos',
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
        $contenido = (array) json_decode($seccion->contenido);
        $data['sections'][$seccion->id] = replace_hashes($contenido['data']);
    }
    $data['seccion'] = $proseccion;
    $app->render('secciones.html', $data);
})->name('admin-secciones');

$app->get('/admin/secciones/editor/:slug', function($slug) use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Secciones", "alias" => "admin-secciones"),
        array("name" => "Editor", "alias" => "editor-seccion")
    );
    $data['secciones'] = Seccion::find('all', array('order' => 'nombre asc'));
    $seccion = Seccion::find_by_slug($slug);
    $contenido = (array) json_decode($seccion->contenido);
    $proseccion = array(
        'id' => $seccion->id,
        'nombre' => $seccion->nombre,
        'slug' => $seccion->slug,
        'contenedor' => $seccion->contenedor,
        'orden' => $seccion->orden,
        'contenido' => $contenido['data'],
        'files' => $contenido['files'],
        'actualizado' => $seccion->actualizado
    );
    $data['seccion'] = $proseccion;
    $files = array();
    $tb = new Toolbox();
    foreach ($contenido['files'] as $file) {
        $size = filesize("./uploads/sections/$file");
        $files[] = array('name'=>$file, 'size' => $tb->bytes2human($size));
    }
    $data['files'] = $files;

    $app->render('editor-seccion.html', $data);
})->name('editor-seccion');

$app->post('/editar-seccion-post/:id/', function($id) use ($app) {
    $validator = new GUMP();
    //$_POST = $validator->sanitize($_POST);
    $rules = array();
    $filters = array();
    $post = $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === true) {
        $seccion = Seccion::find($id);
        $contenido = (array) json_decode($seccion->contenido);
        if(!array_key_exists('data', $contenido)){
            $contenido = array(
                'data' => $_POST['contenido'],
                'files' => array()
            );
        } else {
            $contenido['data'] = $_POST['contenido'];
        }
        $seccion->contenido = json_encode($contenido);
        $seccion->contenedor = (isset($_POST['contenedor'])) ? $_POST['contenedor'] : 0;
        $seccion->actualizado = time();
        $seccion->save();
        $flash = array(
            "title" => "OK",
            "msg" => "Sección Actualizada.",
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
})->name('editar-seccion-post');

//XXX Noticias
$app->get('/admin/noticias/', function() use ($app) {
    $data['user'] = isAllowed("Administrador", false);
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Noticias", "alias" => "admin-noticias")
    );

    $data['noticias'] = $news = Noticia::find('all', array('order' => 'creado desc'));
    foreach ($news as $new) {
        $contenido = (array) json_decode($new->contenido);
        $data['news'][$new->id] = replace_hashes($contenido['data']);
    }
    $app->render('noticias.html', $data);
})->name('admin-noticias');

$app->post('/nueva-noticia/', function() use ($app){
    $tb = new Toolbox();
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array('titulo' => 'required');
    $filters = array('titulo' => 'trim|sanitize_string');
    $post = $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === true) {
        $noticia = new Noticia();
        $noticia->titulo = $_POST['titulo'];
        $noticia->slug = $tb->slugify($_POST['titulo']);
        $contenido = array();
        $contenido['data'] = $_POST['contenido'];
        $contenido['files'] = array();
        $noticia->contenido = json_encode($contenido);
        $noticia->creado = time();
        $noticia->actualizado = time();
        $noticia->save();
        $flash = array(
            "title" => "OK",
            "msg" => "Noticia Creada.",
            "type" => "success",
            "fade" => 1
        );
        $app -> flash("flash", $flash);
        $app->flashKeep();
        $app->redirect($app->urlFor('editor-noticia',array('id'=> $noticia->id)));
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
        $app->redirect($app->urlFor('admin-noticias'));
    }
})->name('nueva-noticia-post');

$app->get('/admin/noticias/editor/:id', function($id) use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Noticias", "alias" => "admin-secciones"),
        array("name" => "Editor", "alias" => "editor-seccion")
    );
    $data['noticias'] = Noticia::find('all', array('order' => 'creado desc'));
    $noticia          = Noticia::find($id);
    $contenido        = (array) json_decode($noticia->contenido);
    $pronoticia       = array(
        'id'          => $noticia->id,
        'titulo'      => $noticia->titulo,
        'slug'        => $noticia->slug,
        'contenido'   => $contenido['data'],
        'files'       => $contenido['files'],
        'imagen'      => $noticia->imagen,
        'creado'      => $noticia->creado,
        'actualizado' => $noticia->actualizado
    );
    $data['noticia'] = $pronoticia;
    $files = array();
    $tb = new Toolbox();
    foreach ($contenido['files'] as $file) {
        $filePath = "./uploads/news/$file";
        $ext      = pathinfo($filePath, PATHINFO_EXTENSION);
        $size     = filesize($filePath);
        $img_exts = array('png','jpg','jpeg');
        $isImg    = (in_array($ext, $img_exts)) ? true : false;
        $starred  = ($noticia->imagen == $file) ? true : false;
        $files[]  = array(
            'name'    =>$file,
            'size'    => $tb->bytes2human($size),
            'img'     => $isImg,
            'starred' => $starred
        );
    }
    $data['files']   = $files;
    $data['numFiles'] = count($files);

    $app->render('editor-noticia.html', $data);
})->name('editor-noticia');

$app->post('/editar-noticia-post/:id/', function($id) use ($app) {
    $tb = new Toolbox();
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'titulo' => 'required'
    );

    $filters = array(
        'titulo' => 'trim|sanitize_string'
    );
    $post = $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === true) {
        $noticia              = Noticia::find($id);
        $contenido            = (array) json_decode($noticia->contenido);
        $contenido['data']    = $_POST['contenido'];
        $noticia->contenido   = json_encode($contenido);
        $noticia->titulo      = $_POST['titulo'];
        $noticia->slug        = $tb->slugify($_POST['titulo']);
        $noticia->actualizado = time();
        $noticia->save();
        $flash = array(
            "title" => "OK",
            "msg" => "Noticia Actualizada.",
            "type" => "success",
            "fade" => 1
        );
        $app -> flash("flash", $flash);
    } else {
        $msgs = humanize_gump($validated);
        $flash = array(
            "title" => "ERROR",
            "msg"   => $msgs,
            "type"  => "error",
            "fade"  => 0
        );
        $app -> flash("flash", $flash);
    }
    $app->flashKeep();
    $app->redirect($app->urlFor('admin-noticias'));
})->name('editar-noticia-post');

$app->get('/borrar-noticia/:id/', function($id) use($app){
    $noticia = Noticia::find($id);
    $noticia->delete();
    $flash = array("title" => "OK", "msg" => "Noticia borrada.", "type" => "info", "fade" => 1);
    $app -> flash("flash", $flash);
    $app -> flashKeep();
    $app -> redirect($app -> urlFor('admin-noticias'));
})->name('borrar-noticia');

$app->get('/admin/catalogos/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Catálogos", "alias" => "admin-catalogos")
    );
    $data['user'] = isAllowed("Administrador", false);
    $app->render('catalogos.html', $data);
})->name('admin-catalogos');

$app->get('/admin/eventos/', function() use($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control","alias" => "admin"),
        array("name" => "Eventos", "alias" => "admin-eventos")
    );
    $data['user'] = isAllowed("Administrador", false);
    $data['no_validados'] = Evento::find_all_by_validado(0);
    $data['validados'] = Evento::find_all_by_validado(1);
    $app->render('validar.html',$data);
})->name('admin-eventos');
?>


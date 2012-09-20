<?php
header('Content-Type: text/html; charset=UTF-8');
ini_set("display_errors",1);
ini_set("memory_limit","1024M");

require __DIR__ . '/vendor/Slim/Slim.php';
require __DIR__ . '/views/TwigView.php';
require __DIR__ . '/vendor/ActiveRecord/ActiveRecord.php';
require __DIR__ . '/vendor/Ladybug/Autoloader.php';
require __DIR__ . '/vendor/GUMP/gump.class.php';
require __DIR__ . '/vendor/Facebook-sdk/facebook.php';
require __DIR__ . '/vendor/Twitter/twitter.class.php';
require __DIR__ . '/vendor/PHPMailer/class.phpmailer.php';
require __DIR__ . '/vendor/elFinder/connector.php';
require __DIR__ . '/vendor/Toolbox.php';
require __DIR__ . '/vendor/DirectoryLister.php';
#require __DIR__ . '/vendor/upload.class.php';
require __DIR__ . '/vendor/pChart/class/pData.class.php';
require __DIR__ . '/vendor/pChart/class/pDraw.class.php';
require __DIR__ . '/vendor/pChart/class/pPie.class.php';
require __DIR__ . '/vendor/pChart/class/pImage.class.php';
require __DIR__ . '/vendor/Faker/autoload.php';
require __DIR__ . '/vendor/ImageWorkshop/src/PHPImageWorkshop/ImageWorkshop.php';
require __DIR__ . '/vendor/Julian/libraries/julian.php';


Ladybug\Autoloader::register();

$connections = array(
    'dev' => 'mysql://root:root@localhost/posgrado',
    'prod' => 'mysql://'.getenv('MYSQL_USERNAME').':'.getenv('MYSQL_PASSWORD').'@'.getenv('MYSQL_DB_HOST').'/'.getenv('MYSQL_DB_NAME').';charset=utf8'
);
ActiveRecord\Config::initialize(function ($cfg) use ($connections) {
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
    if ($validated === true) {
        $user = Usuario::find_by_usuario($_POST['usuario']);
        if (is_object($user)) {

            if ($user->password === $_POST['password']) {
                if ($user->activo == 1) {
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
require 'admin.php';
require 'filesystem.php';
require 'graphs.php';
require 'catalogues.php';

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

$app->get('/estadisticas/nucleo-academico/', function() use ($app) {
    $data['rol'] = Rol::find_by_nombre('Docente');
    $rolus = UsuariosRoles::find_by_rol_id($data['rol']->id);
    $data['usuarios'] = Usuario::find_all_by_id($rolus->usuario_id,array('include'=> array('personal','docente')));
    $app->render('nucleoacademico.html',$data);
})->name('nucleo-academico');
#TODO Verificar publicaciones VS productividad academica
//$app->get('/estadisticas/productividad-academica/', function() use ($app) {
$app->get('/publicaciones/', function() use ($app) {
    /*$rol = Rol::find_by_nombre('Docente');
    $rolus = UsuariosRoles::find_all_by_rol_id($rol->id);
    $idusuarios = array();
    foreach ($rolus as $u) {
        $idusuarios[] = $u->id;
    }
#FIXME Error encontrado al tratar de cargar publicaciones, revisar
    $usuarios = $data['usuarios'] = Usuario::find($idusuarios,array('include'=> array('personal','publicaciones')));
    $publicaciones = array();
    foreach ($usuarios as $usuario) {
        foreach ($usuario->publicaciones as $pub) {
            $nombrecompleto=$usuario->personal->nombre." ".$usuario->personal->paterno." ".$usuario->personal->materno;
            $publicaciones[$pub->tipo][$nombrecompleto][] = $pub;
        }
        $data['pub'] = $publicaciones;

    }
    $data['pub'] = $publicaciones;
    ladybug_dump($data);*/
    $app->render('productividadacademica.html');
})->name('productividad-academica');

$app->get('/calendario/(:year/(:month/))', function($year, $month) use ($app) {
    $year = (is_null($year)) ? date('Y') : $year;
    $month = (is_null($month)) ? date('m') : $month;
    $url = $app->urlFor('calendario',array('year' =>'%y', 'month' => '%m'));
    $calendar = new Julian();
    $calendar->add_event(strtotime('19-09-2012'), strtotime('21-09-2012'), 'Fucking event');
    $calendar->calendar(array(
        'url'           => $url,
        'current_month' => $month,
        'current_year'  => $year
    ));
    $data['calendar'] = $calendar;
//    ladybug_dump(strtotime('19-09-2012'));
//    ladybug_dump(date("d/m/Y",strtotime('19-09-2012')));
//    ladybug_dump($calendar->get_events('19', '09', '2012'));
//    ladybug_dump($calendar);
    $app->render('calendar.html',$data);
})->name('calendario');

$app->get('/relacion-aceptados/', function() use ($app) {
    $app->render('relacion-aceptados.html');
})->name('relacion-aceptados');

$app->get('/publicaciones/', function() use ($app) {
    $user['usuarios'] = Usuario::find_by_id('1');
    $data['publicaciones']= Publicacion::find_all_by_usuario_id($user['usuarios']->id);
    $app->render('publicaciones.html',$data);
})->name('publicaciones');

$app->get('/egresados/', function() use ($app) {
    
})->name('egresados');

$app->get('/estadisticas/matriculacion/', function() use ($app) {
    $app->render('EstadisticaMatriculacion.html');
})->name('matriculacion');

/* =======================
 * ======= DOCENTE =======
 * =======================*/

$app->get('/docente/', function() use ($app) {
     $app->render('docente.html');
})->name('docente');

$app->get('/docente/publicaciones/', function() use ($app) {
    $user ['usuarios']= Usuario::find_by_id('1');
    $data['publicaciones']= Publicacion::find_all_by_usuario_id($user['usuarios']->id);
    #ladybug_dump($data);
     $app->render('publicaciones.html');
 })->name('docente-publicaciones');

 $app->get('/docente/publicaciones-post',function() use ($app) {
        $userid=isAllowed('Docente');
       $validator= new GUMP;
       $_POST=$validator->sanitize($_POST);
        if ($_POST['tipo']==1) {
             $rules=array(
            'mautor'=>'required|alpha',
            'mtitulo'=>'required|alpha',
            'mpublicacion'=>'required|alpha',
            'mfechapublicacion'=>'required',
            );
            $filters=array(

            );
            $_POST = $validator->filter($_POST, $filters);
            $validated = $validator->validate($_POST, $rules);
            if ($validated === TRUE) {
                $pub=new Publicacion;
                $pub->nombre=$_POST['mtitulo'];
                $pub->usuario_id=$userid;
                $pub->coautores=$_POST['mautor'];
                $pub->evento=$_POST['mpublicacion'];
                $pub->fecha_publicacion=$_POST['mfechapublicacion'];
                $pub->tipo_publicacion="Memoria";
                $pub->save();

            $flash = array(
            "title" => "OK",
            "msg" => "Se ha guardado la memoria.",
            "type" => "success",
            "fade" => 1
             );
            $app -> flash("flash", $flash);
            $app->flashKeep();
            $app->redirect($app->urlFor('docente-publicaciones'));

        } else {
           $flash = array(
            "title" => "OK",
            "msg" => "ha ocurrido un problema. Vuelva a intenrar.",
            "type" => "error",
            "fade" => 1
        );
        $app -> flash("flash", $flash);
         $app->flashKeep();
        $app->redirect($app->urlFor('docente-publicaciones'));
        }
        } else {
           if ($_POST['tipo']==2) {
             $rules=array(
            'rautor'=>'required|alpha',
            'rtitulo'=>'required|alpha',
            'revista'=>'required|alpha',
            'rtipo'=>'required',
            'rfechapublicacion'=>'required'
            );
            $filters=array(

            );
            $_POST = $validator->filter($_POST, $filters);
            $validated = $validator->validate($_POST, $rules);
            if ($validated === TRUE) {
                $pub=new Publicacion;
                $pub->nombre=$_POST['rtitulo'];
                $pub->usuario_id=$userid;
                $pub->revista=$_POST['revista'];
                $pub->tipo=$_POST['rtipo'];
                $pub->fecha_publicacion=$_POST['rfrchapublicacion'];
                $pub->tipo_publicacion="Revista";
                $pub->save();

            $flash = array(
            "title" => "OK",
            "msg" => "Se ha guardado la revista.",
            "type" => "success",
            "fade" => 1
             );
            $app -> flash("flash", $flash);
            $app->flashKeep();
            $app->redirect($app->urlFor('docente-publicaciones'));

        } else {
           $flash = array(
            "title" => "OK",
            "msg" => "ha ocurrido un problema. Vuelva a intenrar.",
            "type" => "error",
            "fade" => 1
        );
        $app -> flash("flash", $flash);
         $app->flashKeep();
        $app->redirect($app->urlFor('docente-publicaciones'));
        }
        } else {
          if ($_POST['tipo']==3) {
             $rules=array(
            'lautor'=>'required|alpha',
            'ltitulo'=>'required|alpha',
            'editorial'=>'required|alpha',
            'isbn'=>'required|numeric',
            'lfechapublicacion'=>'required',

            );
            $filters=array(

            );
            $_POST = $validator->filter($_POST, $filters);
            $validated = $validator->validate($_POST, $rules);
            if ($validated === TRUE) {
                $pub=new Publicacion;
                $pub->nombre=$_POST['ltitulo'];
                $pub->usuario_id=$userid;
                $pub->editorial=$_POST['editorial'];
                $pub->fecha_publicacion=$_POST['lfechapublicacion'];
                $pub->tipo_publicacion="Libro";
                $pub->save();

            $flash = array(
            "title" => "OK",
            "msg" => "Se ha guardado el libro.",
            "type" => "success",
            "fade" => 1
             );
            $app -> flash("flash", $flash);
            $app->flashKeep();
            $app->redirect($app->urlFor('docente-publicaciones'));

        } else {
           $flash = array(
            "title" => "OK",
            "msg" => "ha ocurrido un problema. Vuelva a intenrar.",
            "type" => "error",
            "fade" => 1
        );
        $app -> flash("flash", $flash);
         $app->flashKeep();
        $app->redirect($app->urlFor('docente-publicaciones'));
        }
        } else {
           if ($_POST['tipo']==4) {
             $rules=array(
            'tautor'=>'required|alpha',
            'ttitulo'=>'required|alpha',
            'nacionalidad'=>'required|alpha',
            'ttipo'=>'required',
            'tfechapublicacion'=>'required',
            );
            $filters=array(

            );
            $_POST = $validator->filter($_POST, $filters);
            $validated = $validator->validate($_POST, $rules);
            if ($validated === TRUE) {
                $pub=new Publicacion;
                $pub->nombre=$_POST['ttitulo'];
                $pub->usuario_id=$userid;
                $pub->nacionalidad=$_POST['nacionalidad'];
                $pub->tipo=$_POST['ttipo'];
                $pub->tipo_publicacion="Trabajo";
                $pub->save();

            $flash = array(
            "title" => "OK",
            "msg" => "Se ha guardado el trabajo.",
            "type" => "success",
            "fade" => 1
             );
            $app -> flash("flash", $flash);
            $app->flashKeep();
            $app->redirect($app->urlFor('docente-publicaciones'));

        } else {
           $flash = array(
            "title" => "OK",
            "msg" => "ha ocurrido un problema. Vuelva a intenrar.",
            "type" => "error",
            "fade" => 1
        );
        $app -> flash("flash", $flash);
         $app->flashKeep();
        $app->redirect($app->urlFor('docente-publicaciones'));
        }
        }
       }
       }
       }

 })->name('publicaciones-post');

 $app->get('/registro-aspirantes/',function() use ($app) {
     $app->render('registroinicial.html');
 })->name('registro-inicio');

 $app->post('/formulario-registro-post/',function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'nombre' => 'required|alpha',
        'ap'    => 'alpha',
        'am' => 'alpha',
        'email'=>'required|valid_email',
        'usuario' => 'required|alpha',
        'pass'=>'required|alpha',
        'confirmacion'=>'required|alpha',
    );
    $filters = array(

    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === TRUE) {
      if ($_POST['pass']==$_POST['confirmacion']) {
          $personal= new Personal;
          $us=new Usuario;
          $contacto=new Contacto;

          $usuariosroles=new UsuariosRoles;
          $us->login=$_POST['usuario'];
          $us->password=$_POST['pass'];
          $us->activo=1;
          $us->save();

          $personal->nombre=$_POST['nombre'];
          $personal->paterno=$_POST['ap'];
          $personal->materno=$_POST['am'];
          $personal->save();

          $contacto->email=$_POST['email'];
          $contacto->save();

          $flash = array(
            "title" => "OK",
            "msg" => "Se ha creado la cuenta exitosamente.",
            "type" => "success",
            "fade" => 1
          );
          $app -> flash("flash", $flash);
          $app->flashKeep();
          $app->redirect($app->urlFor('registro-aspirantes'));

      } else {
           $flash = array(
        "title" => "OK",
        "msg" => "Los Campos de Confimacion y contraseña deben ser identicos.",
        "type" => "error",
        "fade" => 1
      );
      $app -> flash("flash", $flash);
     $app->flashKeep();
     $app->redirect($app->urlFor('registro-aspirantes'));
     }
    }
 })->name('registro-aspirante-post');

$app->get('/docente/tesistas/', function() use ($app) {

    $data['user'] = isAllowed("Docente",FALSE);
    $rolalumno=Rol::find_by_nombre('Alumno');
	
    $idss=UsuariosRoles::find_all_by_rol_id($rolalumno->id);
    $idalumnos=array();
    foreach ($idss as $u) {
        $idalumnos[] = $u -> usuario_id;
    }
    $alumnos = array();
    $datosalumnos = Usuario::find_all_by_id($idalumnos, array('include' => array('personal')));
    foreach ($datosalumnos as $u) {
        $dato = new Generic;
        $dato -> nombrecompleto = trim($u -> personal -> nombre . " " . $u -> personal -> paterno . " " . $u -> personal -> materno);
        $dato -> id = $u -> id;
        $alumnos[] = $dato;
        unset($dato);
    }
    $data['alumnos']=$alumnos;

    $pos=Posgrado::find_all_by_asesor($data['user']->id);
	if (count($pos)>=1) {
		$data['posgrados']=$pos;
		  $idtesistas = array();
    foreach ($pos as $u) {
        $idtesistas[] = $u -> usuario_id;
    }
	$tesistas = array();
    $datostesista = Usuario::find_all_by_id($idtesistas, array('include' => array('personal')));
    foreach ($datostesista as $u) {
        $dato = new Generic;
        $dato -> nombrecompleto = trim($u -> personal -> nombre . " " . $u -> personal -> paterno . " " . $u -> personal -> materno);
        $dato -> id = $u -> id;
        $tesistas[] = $dato;
        unset($dato);
    }
	$data['datostesistas']=$tesistas;
		
	}
    $data['lineas']= LineaInvestigacion::all();
    
    //ladybug_dump($data['lineas'][0]);
    //ladybug_dump($data['posgrados'][0]);
   $app->render('gestTesista02.html',$data);
})->name('docente-tesistas');

$app->get('/docente/tesistas/:id', function($id) use ($app) {
    $posgrado=Posgrado::find_by_id($id);
	if (count($posgrado)==0) {
	
		$flash = array(
            "title" => "ERROR",
            "msg" => "Se ha borrado el tesista exitosamente.",
            "type" => "error",
            "fade" => 0
          );
		
	} else {
		$posgrado->delete();	
		$flash = array(
            "title" => "OK",
            "msg" => "Se ha borrado el tesista exitosamente.",
            "type" => "success",
            "fade" => 1
          );
		
	}
	
		   $app -> flash("flash", $flash);
          $app->flashKeep();
          $app->redirect($app->urlFor('docente-tesistas'));

})->name('borrar-tesista');

$app->get('/docente/eventos/', function() use ($app) {
    $user ['usuarios'] = Usuario::find_by_id('1');
    $data['eventos'] = Evento::find_all_by_autor($user['usuarios']->id);
    $app->render('eventosDoc.html',$data);
})->name('eventosDoc');

$app->post('/nuevo-tesista/',function() use ($app) {
    $data['user'] = isAllowed("Docente",FALSE);
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
	//ladybug_dump($_POST);
    $rules = array(
        'lineainv'    => 'required|numeric',
        'tesista'    => 'required|numeric',
        'nombreTesis' => 'required',
    );
    $filters = array(
        'nombreTesista' => 'trim',
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === true) {
    	if (isset($_POST['proc'])) {
				
			$posgrado=Posgrado::find_by_usuario_id($_POST['tesista']);
			$posgrado->nombre=$_POST['nombreTesis'];
           $posgrado->asesor=$data['user']->id;
           if (isset($_POST['ingreso'])) {
               $posgrado->asignacion=$_POST['ingreso'];
           }
           if (isset($_POST['finCur'])) {
               $posgrado->fin=$_POST['finCur'];
           }
           if (isset($_POST['ftitulacion'])) {
               $posgrado->titulacion=$_POST['ftitulacion'];
           }
           if (isset($_POST['lineainv'])) {
               $posgrado->linea=$_POST['lineainv'];
           }
		   $posgrado->save();
			
			$flash = array(
            "title" => "OK",
            "msg" => "Se ha actualizado el tesista exitosamente.",
            "type" => "success",
            "fade" => 1
          );
			
		} else {
		
		   $posgrado= new Posgrado;
           $posgrado->usuario_id=$_POST['tesista'];
           $posgrado->nombre=$_POST['nombreTesis'];
           $posgrado->asesor=$data['user']->id;
           if (isset($_POST['ingreso'])) {
               $posgrado->asignacion=$_POST['ingreso'];
           }
           if (isset($_POST['finCur'])) {
               $posgrado->fin=$_POST['finCur'];
           }
           if (isset($_POST['ftitulacion'])) {
               $posgrado->titulacion=$_POST['ftitulacion'];
           }
           if (isset($_POST['lineainv'])) {
               $posgrado->linea=$_POST['lineainv'];
           }      
			$posgrado->save();
		    $flash = array(
            "title" => "OK",
            "msg" => "Se ha agregado el tesista exitosamente.",
            "type" => "success",
            "fade" => 1
          );
			
		}
    } else {

		  $msgs = humanize_gump($validated);
      //   ladybug_dump($msgs);
        $flash = array(
            "title" => "ERROR",
            "msg" => $msgs,
            "type" => "error",
		    "fade" => 0
		);
    }
	 $app -> flash("flash", $flash);
          $app->flashKeep();
          $app->redirect($app->urlFor('docente-tesistas'));
}) -> name('nuevo-tesista-post');

$app->post('/nuevo-documentotesista/',function() use ($app) {
    $data['user'] = isAllowed("Docente",FALSE);
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
    );
    $filters = array(
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === TRUE) {
           $posgrado= new Posgrado;

        $uploaddir = "uploads/";
        $uploadfilename = strtolower(str_replace(" ", "_", basename($_FILES['archivo']['name'])));
        $uploadfile = $uploaddir . $uploadfilename;
        $error = $_FILES['archivo']['error'];
        $subido = false;
        if (isset($_POST['boton']) && $error==UPLOAD_ERR_OK) {
            if ($_FILES['archivo']['type']!="image/gif" || $_FILES['archivo']['size'] > 100000) {
                 $error = "Comprueba que el archivo sea una imagen en formato gif y de tamano inferior a 10Kb.";
            } elseif (preg_match("/[^0-9a-zA-Z_.-]/",$uploadfilename)) {
            $error = "El nombre del archivo contiene caracteres no válidos.";
         } else {
        $subido = copy($_FILES['archivo']['tmp_name'], $uploadfile);
            }
        }
 if ($subido) {
    echo "El archivo subio con exito";
   } else {
    echo "Se ha producido un error: ".$error;
  }

    } else {

    }
})->name('nuevo-documentotesis-post');

$app->get('/docente/perfil/', function() use ($app) {
    $data['user'] = isAllowed("Docente",FALSE);
    $data['usuario'] = Usuario::find(2,array('include' => array('academico','personal','contacto','pg','laboral','docente')));
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
    $data['idiomas']=Idioma::all();
    $data['idiomasusuario']=UsuariosIdiomas::find_all_by_usuario_id(2,array('include' => array('idioma')));
     /* ladybug_dump($data['idiomasusuario']);  */

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
    if ($validated === true) {

    } else {

    }
}) -> name('nuevo-subir-doc-post');

/* =======================
 * === ALUMNO/ASPIRANTE ==
 * =======================*/
$app->get('/editor-perfil/', function() use ($app) {
    $data['user'] = isAllowed(array('Alumno','Aspirante'), false);
    $data['usuario'] = Usuario::find(1, array('include' => array('academico','personal','contacto','pg','laboral','docente')));
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
    $data['areas'] = AreaInteres::all();
    $data['herramientas'] = Herramienta::all();
    $data['idiomas'] = Idioma::all();
    $data['lenguajes'] = Lenguaje::all();
    $data['plataformas'] = Plataforma::all();
    $data['idiomasusuario']=UsuariosIdiomas::find_all_by_usuario_id(1,array('include' => array('idioma')));
    $data['herramientasusuario']=UsuariosHerramientas::find_all_by_usuario_id(1,array('include'=>array('herramienta')));
    $data['plataformasusuario']=UsuariosPlataformas::find_all_by_usuario_id(1,array('include'=>array('plataforma')));
    $data['lenguajesusuario']=UsuariosLenguajes::find_all_by_usuario_id(1,array('include'=>array('lenguaje')));
    $data['areasusuario']=UsuariosAreas::find_all_by_usuario_id(1);
    $data['formas']=FormaTitulacion::all();
    $data['carreras']=Carrera::all();
    $data['ul']=Localidad::find_by_id($data['personal']->procedencia);
    $data['um']=Municipio::find_by_id($data['ul']->clave);
    $data['ue']=Estado::find_by_id($data['um']->clave);
     $data['il']=Localidad::find_by_id($data['academico']->ubicacion);
    $data['im']=Municipio::find_by_id($data['il']->clave);
    $data['ie']=Estado::find_by_id($data['im']->clave);
    $data['formasenterado']=FormaEnterado::all();
    /*ladybug_dump($data);*/

    $app->render('perfilaspirantes.html', $data);
})->name('perfil');

$app->get('/test/', function() use ($app) {
    $data['user'] = isAllowed("Administrador",FALSE);
    ladybug_dump($data);
})->name('test');

$app->post('/nuevo-datos-personales/',function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'nombre'    => 'required',
        'ap'    => 'required',
        'am' => 'required',
        'nacimiento' => 'required',
        'sexo' => 'required|numeric',
        'numex' => 'required|max_len,4|min_len,1',
        'cp' => 'required|numeric|max_len,5|min_len,5',

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

    if ($validated === true) {
        if ( isset($_POST['id'])) {
            $perfilpersonal=Personal::find(1);
            $perfilpersonal->nombre = $_POST['nombre'];
       $perfilpersonal->paterno = $_POST['ap'];
       $perfilpersonal->materno = $_POST['am'];
       $perfilpersonal->fdn = $_POST['nacimiento'];
       $perfilpersonal->sexo = $_POST['sexo'];
       $perfilpersonal->procedencia = $_POST['nlocalidad'];

       $perfilpersonal->calle = $_POST['calle'];
       $perfilpersonal->num_ext = $_POST['numex'];
       if (isset($_POST['numint'])) {
                 $perfilpersonal->num_int = $_POST['numint'];
       }

       $perfilpersonal->colonia = $_POST['colonia'];
       $perfilpersonal->cp = $_POST['cp'];
       $perfilpersonal->save();
        } else {
            $perfilpersonal =  new Personal();
       $perfilpersonal->nombre = $_POST['nombre'];
       $perfilpersonal->paterno = $_POST['ap'];
       $perfilpersonal->materno = $_POST['am'];
       $perfilpersonal->fdn = $_POST['nacimiento'];
       $perfilpersonal->sexo = $_POST['sexo'];
       $perfilpersonal->procedencia = $_POST['nlocalidad'];
       $perfilpersonal->calle = $_POST['calle'];
       $perfilpersonal->num_ext = $_POST['numex'];
       if (isset($_POST['numint'])) {
       $perfilpersonal->num_int = $_POST['numint'];
       }

       $perfilpersonal->colonia = $_POST['colonia'];
       $perfilpersonal->cp = $_POST['cp'];
       $perfilpersonal->save();
        }
        $flash = array("title" => "OK", "msg" => "Datos personales guardados correctamente.", "type" => "success", "fade" => 1);

        $app -> flash("flash", $flash);
        $app -> flashKeep();
        if ($_POST['perfil'] == 1) {
            $app -> redirect($app -> urlFor('perfil'));
        } else {
            $app -> redirect($app -> urlFor('perfil-docente'));
        }
    } else {

        $msgs = humanize_gump($validated);
         ladybug_dump($msgs);
        $flash = array(
            "title" => "ERROR",
            "msg" => $msgs,
            "type" => "error",
            "fade" => 0
        );

        $app -> flash("flash", $flash);
        $app -> flashKeep();
        if ($_POST['perfil'] == 1) {
            $app -> redirect($app -> urlFor('perfil'));
        } else {
            $app -> redirect($app -> urlFor('perfil-docente'));
        }
    }
}) -> name('nuevo-datospersonales-post');

$app->post('/nuevo-datos-academicos/',function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'institucion' => 'required',
        'carrera'    => 'required',
        'forma' => 'required',
        'ingreso' => 'required',
        'egreso' => 'required',
        'localidad' => 'required|numeric|max_len,4|min_len,1',

    );
    $filters = array(
        'institucion' => 'trim',
        'carrera'    => 'trim',
        'forma' => 'trim',
        'ingreso' => 'trim',
        'egreso' => 'trim',
        'localidad' => 'trim',
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === true) {
        if (isset($_POST['id'])) {
            $perfilacademico = Academico::find($_POST['id']);
            $perfilacademico -> institucion = $_POST['institucion'];
            $perfilacademico -> carrera = $_POST['carrera'];
            $perfilacademico -> ingreso = $_POST['ingreso'];
            $perfilacademico -> egreso = $_POST['egreso'];
            $perfilacademico -> titulacion = $_POST['forma'];
            $perfilacademico -> ubicacion = $_POST['localidad'];
            $perfilacademico -> save();
        } else {
            $perfilacademico = new Academico();
            $perfilacademico -> usuario_id = $_POST['user'] -> id;
            $perfilacademico -> institucion = $_POST['institucion'];
            $perfilacademico -> carrera = $_POST['carrera'];
            $perfilacademico -> ingreso = $_POST['ingreso'];
            $perfilacademico -> egreso = $_POST['egreso'];
            $perfilacademico -> titulacion = $_POST['forma'];
            $perfilacademico -> ubicacion = $_POST['localidad'];
            $perfilacademico -> save();
        }
        $flash = array("title" => "OK", "msg" => "Datos académicos guardados correctamente.", "type" => "success", "fade" => 1);

        $app -> flash("flash", $flash);
        $app -> flashKeep();
        if ($_POST['perfil'] == 1) {
            $app -> redirect($app -> urlFor('perfil'));
        } else {
            $app -> redirect($app -> urlFor('perfil-docente'));
        }
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        if ($_POST['perfil'] == 1) {
            $app -> redirect($app -> urlFor('perfil'));
        } else {
            $app -> redirect($app -> urlFor('perfil-docente'));
        }
    }
}) -> name('nuevo-datosacademicos-post');

$app->post('/nuevo-info-contacto/',function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'email' => 'required',
        'enterado' => 'required',
        'mantener' => 'required',
        'fijo' => 'required',
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
    if ($validated === true) {
        if ($_POST['id']) {
           $perfilinfo = Contacto::find($_POST['id']);
        $perfilinfo->email = $_POST['email'];
        $perfilinfo->movil = $_POST['movil'];
        $perfilinfo->fijo = $_POST['fijo'];
        $perfilinfo->contactar = $_POST['mantener'];
        $perfilinfo->forma = $_POST['enterado'];
        $perfilinfo->save();
        } else {
            $perfilinfo = new Contacto();
        $perfilinfo->email = $_POST['email'];
        $perfilinfo->movil = $_POST['movil'];
        $perfilinfo->fijo = $_POST['fijo'];
        $perfilinfo->contactar = $_POST['mantener'];
        $perfilinfo->forma = $_POST['enterado'];
        $perfilinfo->save();
        }

    $flash = array(
            "title" => "OK",
            "msg" => "Datos de contacto guardados correctamente.",
            "type" => "success",
            "fade" => 1
        );

        $app -> flash("flash", $flash);
        $app->flashKeep();
        $app->redirect($app->urlFor('perfil'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        if ($_POST['perfil'] == 1) {
            $app -> redirect($app -> urlFor('perfil'));
        } else {
            $app -> redirect($app -> urlFor('perfil-docente'));
        }
    }
}) -> name('nuevo-infocontacto-post');

$app->post('/nuevo-experiencia-laboral/',function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
    );
    $filters = array(
        'explab' => 'sanitize_string',
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === true) {
        if (isset($_POST['id'])) {
             $perfillaboral =Laboral::find($_POST['id']);
        $perfillaboral->trabajado = $_POST['trabajo'];
        $perfillaboral->experiencia = $_POST['explab'];
        $perfillaboral->tiempo = $_POST['anostrabajo'];
        $perfillaboral->usuario_id = 1;
        $perfillaboral->save();
        } else {
           $perfillaboral = new Laboral();
        $perfillaboral->trabajado = $_POST['trabajo'];
        $perfillaboral->experiencia = $_POST['explab'];
        $perfillaboral->tiempo = $_POST['anostrabajo'];
        $perfillaboral->usuario_id = 1;
        $perfillaboral->save();
        }

    $flash = array(
            "title" => "OK",
            "msg" => "Datos de experiencia laboral guardados correctamente.",
            "type" => "success",
            "fade" => 1
        );

        $app -> flash("flash", $flash);
        $app->flashKeep();
        $app->redirect($app->urlFor('perfil'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        if ($_POST['perfil'] == 1) {
            $app -> redirect($app -> urlFor('perfil'));
        } else {
            $app -> redirect($app -> urlFor('perfil-docente'));
        }
    }
}) -> name('nuevo-explaboral-post');

$app->post('/nuevo-datos-docente/',function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        'grado'    => 'required',
        'cedula'    => 'required|alpha_numeric',
        'especialidad'    => 'required',
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
    if ($validated === true) {
        if ($_POST['id']) {
            $perfldocente=Docente::find($_POST['id']);
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
        }

    $flash = array(
            "title" => "OK",
            "msg" => "Datos de docente guardados correctamente.",
            "type" => "success",
            "fade" => 1
        );

        $app -> flash("flash", $flash);
        $app->flashKeep();
        $app->redirect($app->urlFor('perfil-docente'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('perfil-docente'));
    }
}) -> name('nuevo-datosdocente-post');

$app->post('/nuevo-conocimiento/',function() use ($app) {

    $data['user'] = isAllowed(array("Docente","Alumno"),FALSE);
    $validator = new GUMP();
    $_POST = $validator->sanitize($_POST);
    $rules = array(
        );
    $filters = array(
    );
    $_POST = $validator->filter($_POST, $filters);
    $validated = $validator->validate($_POST, $rules);
    if ($validated === TRUE) {

        if (isset($_POST['area'])) {
            foreach ($_POST['area'] as $area) {
             $areainteres=new UsuariosAreas;
             $areainteres->usuario_id=1;
             $areainteres->area_id=$_POST['area'];
             $areainteres->save();
            }
        }
        if (isset($_POST['lenguaje'])) {
            foreach ($_POST['lenguaje'] as $lenguaje) {
                $lenguajes=new UsuariosLenguajes;
                $lenguajes->usuario_id=1/*$data['user']->id*/;
                $lenguajes->lenguaje_id=$lenguaje;
                $lenguajes->save();
            }
        }
        if (isset($_POST['herramienta'])) {
            foreach ($_POST['herramienta'] as $herramienta) {
                $herr = Herramienta::find_by_id($herramienta);
                if (is_null($herr)) {
                    $herramientas = new UsuariosHerramientas;
                    $herramientas -> usuario_id = $data['user'] -> id;
                    $herramientas -> herramienta_id = $herramienta;
                    $herramientas -> save();
                }

            }
        }
        if (isset($_POST['plataformas'])) {
            foreach ($_POST['plataformas'] as $plataformas) {
                $plataforma=new UsuariosPlataformas;
                $plataforma->usuario_id=1;
                $plataforma->plataforma_id=$plataformas;
                $plataforma->save();
            }

        }

        $flash = array("title" => "OK", "msg" => "El idioma se ha guardado correctamente.", "type" => "success", "fade" => 1);

        $app -> flash("flash", $flash);
        $app -> flashKeep();
        if ($_POST['perfil'] == 1) {
            $app -> redirect($app -> urlFor('perfil'));
        } else {
            $app -> redirect($app -> urlFor('perfil-docente'));
        }
        /*ladybug_dump($_POST);*/
    } else {

    }
}) -> name('nuevo-idioma-usuario-post');

/* =======================
 * ====== PRINCIPAL ======
 * =======================*/
$app->get('/(:slug/)', function ($slug = "") use ($app) {
    $data['user'] = isAllowed("Administrador", false);
    if ($slug != "") {
        $seccion = Seccion::find_by_slug($slug);
        if ($seccion) {
            $contenido = (array) json_decode($seccion->contenido);
            $proseccion = array(
                'id' => $seccion->id,
                'nombre' => $seccion->nombre,
                'slug' => $seccion->slug,
                'contenedor' => $seccion->contenedor,
                'orden' => $seccion->orden,
                'contenido' => replace_hashes($contenido['data']),
                'files' => $contenido['files'],
                'actualizado' => $seccion->actualizado
            );
            $data['seccion'] = $proseccion;
            $files = (array) $contenido['files'];
            $imgs = array();
            $upload_path = "./uploads/sections/";
            foreach($files as $file){
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $filename = pathinfo($file, PATHINFO_BASENAME);
                $img_exts = array('png','jpg','gif','jpeg','bmp');
                if(in_array($ext, $img_exts)){
                    $imgs[] = $file;
                    if(!file_exists($upload_path."thumb_$filename")){
                        $layer = new PHPImageWorkshop\ImageWorkshop(array('imageFromPath' => $upload_path.$file));
                        $layer->cropMaximumInPixel(0, 0, "MM");
                        $layer->resizeInPixel(200, 200);
                        $layer->save($upload_path, "thumb_$filename", true, null, 95);
                        @chmod($upload_path."thumb_$filename", 0777);
                    }
                }
            }
            $data['imgs'] = $imgs;
            $data['files'] = $files;
        }
    } else {
        $images = glob("img/gallery/{*.jpg,*.gif,*.png}", GLOB_BRACE);
        $data['imgs'] = $images;
    }
    $app->render('index.html', $data);
})->name('home');

require 'functions.inc.php';

$app->run();
<?php
#XXX Areas de Interes
$app -> get('/admin/catalogos/areas-interes/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control", "alias" => "admin"),
        array("name" => "Catálogos", "alias" => "admin-catalogos"),
        array("name" => "Areas de Interes", "alias" => "CatAreaInteres")
    );
    $data['areas_interes'] = AreaInteres::all();
    $app -> render('areasinteres.html', $data);
}) -> name('CatAreaInteres');

$app -> post('/nueva-area-interes/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === true) {
        $ainteres = new AreaInteres();
        $ainteres -> nombre = $_POST['nombre'];
        $ainteres -> save();

        $flash = array("title" => "OK", "msg" => "Area de Interés se agregó satisfactoriamente .", "type" => "success", "fade" => 1);

        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatAreaInteres'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatAreaInteres'));
    }
}) -> name('nueva-ainteres-post');

$app -> post('/actualiza-area-interes/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,100|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === true) {
        $ainteres = AreaInteres::find($id);
        $ainteres -> nombre = $_POST['nombre-edit'];
        $ainteres -> save();
        $flash = array("title" => "OK", "msg" => "Datos del Área de Interés han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatAreaInteres'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatAreaInteres'));
    }
}) -> name('actualiza-ainteres-post');

$app -> get('/borrar-area-interes/:id/', function($id) use ($app) {
    $relaciones = UsuariosAreas::find_all_by_area_id($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
        $ainteres = AreaInteres::find($id);
        $ainteres -> delete();
        $flash = array("title" => "OK", "msg" => "El Area de Interés ha sido borrada correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatAreaInteres'));
    } else {
        $flash = array("title" => "OK", "msg" => "El Area de Interes esta relacionado, no se permite la eliminación.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatAreaInteres'));
    }
}) -> name('borrar-ainteres');

#XXX Idiomas
$app -> get('/admin/catalogos/idiomas/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control", "alias" => "admin"),
        array("name" => "Catálogos", "alias" => "admin-catalogos"),
        array("name" => "Idiomas", "alias" => "CatIdiomas")
    );
    $data['idiomas'] = Idioma::all();
    $app -> render('idiomas.html', $data);
}) -> name('CatIdiomas');

$app -> post('/nuevo-idioma/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === true) {
        $idiomas = new Idioma();
        $idiomas -> nombre = $_POST['nombre'];
        $idiomas -> save();
        $flash = array("title" => "OK", "msg" => "Los datos se han guardado correctamente.", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatIdiomas'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatIdiomas'));
    }
}) -> name('nuevo-idioma-post');

$app -> post('/actualiza-idioma/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,100|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === true) {
        $idioma = Idioma::find($id);
        $idioma -> nombre = $_POST['nombre-edit'];
        $idioma -> save();
        $flash = array("title" => "OK", "msg" => "Los datos se han actualizado correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatIdiomas'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatIdiomas'));
    }
}) -> name('actualiza-idioma-post');

$app -> get('/borrar-idioma/:id/', function($id) use ($app) {
    $relaciones = UsuariosIdiomas::find_all_by_idioma_id($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
        $ainteres = Idioma::find($id);
        $ainteres -> delete();
        $flash = array("title" => "OK", "msg" => "El idioma ha sido borrado correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatIdiomas'));
    } else {
        $flash = array("title" => "OK", "msg" => "El Idioma esta relacionado, no se permite la eliminación.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatIdiomas'));
    }
}) -> name('borrar-idioma');

#XXX Lenguajes
$app -> get('/admin/catalogos/lenguajes/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control", "alias" => "admin"),
        array("name" => "Catálogos", "alias" => "admin-catalogos"),
        array("name" => "Lenguajes", "alias" => "CatLenguajes")
    );
    $data['lenguajes'] = Lenguaje::all();
    $app -> render('lenguajes.html', $data);
}) -> name('CatLenguajes');

$app -> post('/nuevo-lenguaje/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === true) {
        $ainteres = new Lenguaje();
        $ainteres -> nombre = $_POST['nombre'];
        $ainteres -> save();
        $flash = array("title" => "OK", "msg" => "Lenguaje se agregó satisfactoriamente .", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatLenguajes'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatLenguajes'));
    }
}) -> name('nuevo-lenguaje-post');

$app -> post('/actualiza-lenguaje/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,100|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === true) {
        $lenguaje = Lenguaje::find($id);
        $lenguaje -> nombre = $_POST['nombre-edit'];
        $lenguaje -> save();
        $flash = array("title" => "OK", "msg" => "Datos del Lenguaje han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatLenguajes'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatLenguajes'));
    }
}) -> name('actualiza-lenguaje-post');

$app -> get('/borrar-lenguaje/:id/', function($id) use ($app) {
    $relaciones = UsuariosLenguajes::find_all_by_lenguaje_id($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
        $ainteres = Lenguaje::find($id);
        $ainteres -> delete();
        $flash = array("title" => "OK", "msg" => "El Lenguaje ha sido borrada correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatLenguajes'));
    } else {
        $flash = array("title" => "OK", "msg" => "El Lenguaje esta relacionado, no se permite la eliminación.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatLenguajes'));
    }
}) -> name('borrar-lenguaje');

#XXX Plataformas
$app -> get('/admin/catalogos/plataformas/', function() use ($app) {
    $data['breadcrumb'] = array(
        array("name" => "Panel de Control", "alias" => "admin"),
        array("name" => "Catálogos", "alias" => "admin-catalogos"),
        array("name" => "Plataformas", "alias" => "CatPlataformas")
    );
    $data['plataformas'] = Plataforma::all();
    $app -> render('plataformas.html', $data);
}) -> name('CatPlataformas');

$app -> post('/nueva-plataforma/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === true) {
        $ainteres = new Plataforma();
        $ainteres -> nombre = $_POST['nombre'];
        $ainteres -> save();
        $flash = array("title" => "OK", "msg" => "La Plataforma se agregó satisfactoriamente .", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatPlataformas'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatPlataformas'));
    }
}) -> name('nueva-plataforma-post');

$app -> post('/actualiza-plataforma/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === true) {
        $plataforma = Plataforma::find($id);
        $plataforma -> nombre = $_POST['nombre-edit'];
        $plataforma -> save();
        $flash = array("title" => "OK", "msg" => "Datos de la Plataforma han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatPlataformas'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatPlataformas'));
    }
}) -> name('actualiza-plataforma-post');

$app -> get('/borrar-plataforma/:id/', function($id) use ($app) {
    $relaciones = UsuariosPlataformas::find_all_by_plataforma_id($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
        $ainteres = Plataforma::find($id);
        $ainteres -> delete();
        $flash = array("title" => "OK", "msg" => "La Plataforma ha sido borrada correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatPlataformas'));
    } else {
        $flash = array("title" => "OK", "msg" => "La Plataforma esta relacionado, no se permite la eliminación.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatPlataformas'));
    }
}) -> name('borrar-plataforma');

#XXX Formas de enterado
$app -> get('/admin/catalogos/formas-enterado/', function() use ($app) {
    $data['breadcrumb'] = array( array("name" => "Panel de Control", "alias" => "admin"), array("name" => "Catálogos", "alias" => "admin-catalogos"), array("name" => "Formas de Enterado", "alias" => "CatFormasEnterado"));
    $data['forma_enterado'] = FormaEnterado::all();
    $app -> render('formas_enterado.html', $data);
}) -> name('CatFormasEnterado');

$app -> post('/nuevo-formasent/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $formaent = new FormaEnterado();
        $formaent -> nombre = $_POST['nombre'];
        $formaent -> save();
        $flash = array("title" => "OK", "msg" => "Forma de Enterado se agregó satisfactoriamente .", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatFormasEnterado'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatFormasEnterado'));
    }
}) -> name('nuevo-formaent-post');

$app -> post('/actualiza-formasent/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $formaent = FormaEnterado::find($id);
        $formaent -> nombre = $_POST['nombre-edit'];
        $formaent -> save();
        $flash = array("title" => "OK", "msg" => "Datos de la Forma de Enterado han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatFormasEnterado'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatFormasEnterado'));
    }
}) -> name('actualiza-formaent-post');

$app -> get('/borrar-formaent/:id/', function($id) use ($app) {
    $formaent = FormaEnterado::find($id);
    $formaent -> delete();
    $flash = array("title" => "OK", "msg" => "Forma de Enterado ha sido borrado correctamente.", "type" => "info", "fade" => 1);
    $app -> flash("flash", $flash);
    $app -> flashKeep();
    $app -> redirect($app -> urlFor('CatFormasEnterado'));
}) -> name('borrar-formaent');

#XXX Herramientas
$app -> get('/admin/catalogos/herramientas/', function() use ($app) {
    $data['breadcrumb'] = array( array("name" => "Panel de Control", "alias" => "admin"), array("name" => "Catálogos", "alias" => "admin-catalogos"), array("name" => "Herramientas", "alias" => "CatHerramienta"));
    $data['herramientas'] = Herramienta::all();
    $app -> render('herramientas.html', $data);
}) -> name('CatHerramienta');

$app -> post('/nueva-herramienta/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $herramienta = new Herramienta();
        $herramienta -> nombre = $_POST['nombre'];
        $herramienta -> save();
        $flash = array("title" => "OK", "msg" => "La Herramienta se agregó satisfactoriamente .", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatHerramienta'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatHerramienta'));
    }
}) -> name('nueva-herramienta-post');

$app -> post('/actualiza-herramienta/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $herramienta = Herramienta::find($id);
        $herramienta -> nombre = $_POST['nombre-edit'];
        $herramienta -> save();
        $flash = array("title" => "OK", "msg" => "Datos de la Herramienta han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatHerramienta'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatHerramienta'));
    }
}) -> name('actualiza-herramienta-post');

$app -> get('/borrar-herramienta/:id/', function($id) use ($app) {
    $relaciones = UsuariosHerramientas::find_all_by_herramienta_id($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
        $herramienta = Herramienta::find($id);
        $herramienta -> delete();
        $flash = array("title" => "OK", "msg" => "La Herramienta ha sido borrada correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatHerramienta'));
    } else {
        $flash = array("title" => "OK", "msg" => "La Herramienta esta relacionado, no se permite la eliminación.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatHerramienta'));
    }
}) -> name('borrar-herramienta');

#XXX Lineas de Investigación
$app -> get('/admin/catalogos/lineas-investigacion/', function() use ($app) {
    $data['breadcrumb'] = array( array("name" => "Panel de Control", "alias" => "admin"), array("name" => "Catálogos", "alias" => "admin-catalogos"), array("name" => "Líneas de Investigación", "alias" => "CatLineaInv"));
    $data['lineas_investigacion'] = LineaInvestigacion::all();
    $app -> render('lineas_investigacion.html', $data);
}) -> name('CatLineaInv');

$app -> post('/nueva-lineainv/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,100|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $lineainv = new LineaInvestigacion();
        $lineainv -> nombre = $_POST['nombre'];
        $lineainv -> save();
        $flash = array("title" => "OK", "msg" => "La línea de investigación se agregó satisfactoriamente.", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatLineaInv'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatLineaInv'));
    }
}) -> name('nueva-lineainv-post');

$app -> post('/actualiza-lineainv/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,100|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $lineainv = LineaInvestigacion::find($id);
        $lineainv -> nombre = $_POST['nombre-edit'];
        $lineainv -> save();
        $flash = array("title" => "OK", "msg" => "Los datos de la línea de investigación han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatLineaInv'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatLineaInv'));
    }
}) -> name('actualiza-lineainv-post');

$app -> get('/borrar-lineainv/:id/', function($id) use ($app) {
    $relaciones = Materias::find_all_by_linea_investigacion($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
        $lineainv = LineaInvestigacion::find($id);
        $lineainv -> delete();
        $flash = array("title" => "OK", "msg" => "La línea de investigación ha sido borrada correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatLineaInv'));
    } else {
        $flash = array("title" => "OK", "msg" => "La línea de investigación esta relacionada, no se permite la eliminación.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatInstitucion'));
    }
}) -> name('borrar-lineainv');

#XXX Instituciones
$app -> get('/admin/catalogos/instituciones/', function() use ($app) {
    $data['breadcrumb'] = array( array("name" => "Panel de Control", "alias" => "admin"), array("name" => "Catálogos", "alias" => "admin-catalogos"), array("name" => "Instituciones", "alias" => "CatInstitucion"));
    $data['instituciones'] = Institucion::all();
    $app -> render('instituciones.html', $data);
}) -> name('CatInstitucion');

$app -> post('/nueva-institucion/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,100|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $institucion = new Institucion();
        $institucion -> nombre = $_POST['nombre'];
        $institucion -> abreviatura = $_POST['abrev'];
        $institucion -> save();
        $flash = array("title" => "OK", "msg" => "La institución se agregó satisfactoriamente .", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatInstitucion'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatInstitucion'));
    }
}) -> name('nueva-institucion-post');

$app -> post('/actualiza-institucion/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,100|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $institucion = Institucion::find($id);
        $institucion -> nombre = $_POST['nombre-edit'];
        $institucion -> abreviatura = $_POST['abrev-edit'];
        $institucion -> save();
        $flash = array("title" => "OK", "msg" => "Los datos de la institución han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatInstitucion'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatInstitucion'));
    }
}) -> name('actualiza-institucion-post');

$app -> get('/borrar-institucion/:id/', function($id) use ($app) {
    $relaciones = Academico::find_all_by_institucion($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
        $herramienta = Institucion::find($id);
        $herramienta -> delete();
        $flash = array("title" => "OK", "msg" => "La institución ha sido borrada correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatInstitucion'));
    } else {
        $flash = array("title" => "OK", "msg" => "La institución esta relacionada, no se permite la eliminación.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatInstitucion'));
    }
}) -> name('borrar-institucion');

#XXX Roles
$app -> get('/admin/roles/', function() use ($app) {
    $data['roles'] = Rol::all();
    $app -> render('roles.html', $data);
}) -> name('CatRol');

$app -> post('/nuevo-rol/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,100|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $rol = new Rol();
        $rol -> nombre = $_POST['nombre'];
        $rol -> save();
        $flash = array("title" => "OK", "msg" => "El Rol se agregó satisfactoriamente .", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatRol'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatRol'));
    }
}) -> name('nuevo-rol-post');

$app -> post('/actualiza-rol/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,100|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $rol = Rol::find($id);
        $rol -> nombre = $_POST['nombre-edit'];
        $rol -> save();
        $flash = array("title" => "OK", "msg" => "Datos del rol han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatRol'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatRol'));
    }
}) -> name('actualiza-rol-post');

$app -> get('/borrar-rol/:id/', function($id) use ($app) {
    $relaciones = UsuariosRoles::find_all_by_rol_id($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
        $herramienta = Rol::find($id);
        $herramienta -> delete();
        $flash = array("title" => "OK", "msg" => "El Rol ha sido borrada correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatRol'));
    } else {
        $flash = array("title" => "OK", "msg" => "El Rol esta relacionado, no se permite la eliminación.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatRol'));
    }
}) -> name('borrar-rol');

#XXX Eventos
$app -> get('/catalogos/eventos/', function() use ($app) {
    $data['eventos'] = Evento::all();
    $app -> render('nuevoevento.html', $data);
}) -> name('CatEvento');

$app -> post('/nuevo-evento/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,100|min_len,1', 'autor' => 'required|max_len,50|min_len,1', 'descripcion' => 'required|max_len,100|min_len,1', 'fecha_inicio' => 'required|max_len,100|min_len,1', 'fecha_fin' => 'required|max_len,100|min_len,1', 'prioridad' => 'required|max_len,100|min_len,1', 'fecha_creado' => 'required|max_len,10|min_len,1', 'hora_inicio' => 'required|max_len,10|min_len,1', 'hora_fin' => 'required|max_len,100|min_len,1', 'validado' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $evento = new Evento();
        $evento -> nombre = $_POST['nombre'];
        $evento -> autor = $_POST['autor'];
        $evento -> descripcion = $_POST['descripcion'];
        $evento -> fecha_inicio = $_POST['fecha_inicio'];
        $evento -> fecha_fin = $_POST['fecha_fin'];
        $evento -> prioridad = $_POST['prioridad'];
        $evento -> fecha_creado = $_POST['fecha_creado'];
        $evento -> hora_inicio = $_POST['hora_inicio'];
        $evento -> hora_fin = $_POST['hora_fin'];
        $evento -> validado = $_POST['validado'];
        $evento -> save();
        $flash = array("title" => "OK", "msg" => "El evento se agregó satisfactoriamente .", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatEvento'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatEvento'));
    }
}) -> name('nuevo-evento-post');

$app -> post('/actualiza-evento/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,100|min_len,1', 'autor-edit' => 'required|max_len,50|min_len,1', 'descripcion-edit' => 'required|max_len,100|min_len,1', 'fecha_inicio-edit' => 'required|max_len,100|min_len,1', 'fecha_fin-edit' => 'required|max_len,100|min_len,1', 'prioridad-edit' => 'required|max_len,100|min_len,1', 'fecha_creado-edit' => 'required|max_len,10|min_len,1', 'hora_inicio-edit' => 'required|max_len,10|min_len,1', 'hora_fin-edit' => 'required|max_len,100|min_len,1', 'validado-edit' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $evento = Evento::find($id);
        $evento -> nombre = $_POST['nombre-edit'];
        $evento -> autor = $_POST['autor-edit'];
        $evento -> descripcion = $_POST['descripcion-edit'];
        $evento -> fecha_inicio = $_POST['fecha_inicio-edit'];
        $evento -> fecha_fin = $_POST['fecha_fin-edit'];
        $evento -> prioridad = $_POST['prioridad-edit'];
        $evento -> fecha_creado = $_POST['fecha_creado-edit'];
        $evento -> hora_inicio = $_POST['hora_inicio-edit'];
        $evento -> hora_fin = $_POST['hora_fin-edit'];
        $evento -> validado = $_POST['validado-edit'];
        $evento -> save();
        $flash = array("title" => "OK", "msg" => "Datos del evento han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatEvento'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatEvento'));
    }
}) -> name('actualiza-evento-post');

$app -> get('/borrar-evento/:id/', function($id) use ($app) {

    $relaciones = UsuariosEventos::find_all_by_evento_id($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {

        $herramienta = Evento::find($id);
        $herramienta -> delete();
        $flash = array("title" => "OK", "msg" => "El evento ha sido borrada correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatEvento'));

    } else {
        $flash = array("title" => "OK", "msg" => "El evento esta relacionado, no se permite la eliminación.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatEvento'));
    }

}) -> name('borrar-evento');

#XXX Materias
$app -> get('/admin/catalogos/materias/', function() use ($app) {
    $data['breadcrumb'] = array( array("name" => "Panel de Control", "alias" => "admin"), array("name" => "Catálogos", "alias" => "admin-catalogos"), array("name" => "Areas de Interes", "alias" => "CatMateria"));
    $data['materias'] = Materia::all();
    $data['lineas_investigacion'] = LineaInvestigacion::find('all', array('order' => 'nombre asc'));
    $app -> render('materias.html', $data);
}) -> name('CatMateria');

$app -> post('/nueva-materia/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,100|min_len,1', 'semestre' => 'required|numeric|max_len,2|min_len,1', 'linea_investigacion' => 'required|max_len,100|min_len,1', 'doc' => 'required|max_len,100|min_len,1', 'tis' => 'required|max_len,100|min_len,1', 'tps' => 'required|max_len,100|min_len,1', 'horas_totales' => 'required|numeric|max_len,4|min_len,1', 'creditos' => 'required|numeric|max_len,3|min_len,1', 'link_pdf' => 'required|max_len,100|min_len,1', 'tipo' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $materia = new Materia();
        $materia -> nombre = $_POST['nombre'];
        $materia -> semestre = $_POST['semestre'];
        $materia -> linea_investigacion = $_POST['linea_investigacion'];
        $materia -> doc = $_POST['doc'];
        $materia -> tis = $_POST['tis'];
        $materia -> tps = $_POST['tps'];
        $materia -> horas_totales = $_POST['horas_totales'];
        $materia -> creditos = $_POST['creditos'];
        $materia -> link_pdf = $_POST['link_pdf'];
        $materia -> tipo = $_POST['tipo'];
        $materia -> save();
        $flash = array("title" => "OK", "msg" => "La Materia se agregó satisfactoriamente .", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatMateria'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatMateria'));
    }
}) -> name('nueva-materia-post');

$app -> post('/actualiza-materia/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,100|min_len,1', 'semestre-edit' => 'required|numeric|max_len,2|min_len,1', 'linea_investigacion-edit' => 'required|max_len,100|min_len,1', 'doc-edit' => 'required|max_len,100|min_len,1', 'tis-edit' => 'required|max_len,100|min_len,1', 'tps-edit' => 'required|max_len,100|min_len,1', 'horas_totales-edit' => 'required|numeric|max_len,4|min_len,1', 'creditos-edit' => 'required|numeric|max_len,3|min_len,1', 'link_pdf-edit' => 'required|max_len,100|min_len,1', 'tipo-edit' => 'required|max_len,10|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
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
        $flash = array("title" => "OK", "msg" => "Datos de la Materia han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatMateria'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatMateria'));
    }
}) -> name('actualiza-materia-post');

$app -> get('/borrar-materia/:id/', function($id) use ($app) {
    /*
     $relaciones = Academico::find_all_by_institucion($id);
     $cant_relaciones = count($relaciones);
     if ($cant_relaciones == 0) {
     */
    $herramienta = Materia::find($id);
    $herramienta -> delete();
    $flash = array("title" => "OK", "msg" => "La Materia ha sido borrada correctamente.", "type" => "info", "fade" => 1);
    $app -> flash("flash", $flash);
    $app -> flashKeep();
    $app -> redirect($app -> urlFor('CatMateria'));
    /*
     } else {
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
}) -> name('borrar-materia');

#XXX Carreras
$app -> get('/admin/catalogos/carreras/', function() use ($app) {
    $data['carreras'] = Carrera::all();
    $app -> render('carreras.html', $data);
}) -> name('CatCarrera');

$app -> post('/nueva-carrera/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $carrera = new Carrera();
        $carrera -> nombre = $_POST['nombre'];
        $carrera -> save();
        $flash = array("title" => "OK", "msg" => "La Carrera se agregó satisfactoriamente .", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatCarrera'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatCarrera'));
    }
}) -> name('nueva-carrera-post');

$app -> post('/actualiza-carrera/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $carrera = Carrera::find($id);
        $carrera -> nombre = $_POST['nombre-edit'];
        $carrera -> save();
        $flash = array("title" => "OK", "msg" => "Datos de la Carrera han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatCarrera'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatCarrera'));
    }
}) -> name('actualiza-carrera-post');

$app -> get('/borrar-carrera/:id/', function($id) use ($app) {
    $relaciones = Academico::find_all_by_carrera($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
        $carrera = Carrera::find($id);
        $carrera -> delete();
        $flash = array("title" => "OK", "msg" => "La Carrera ha sido borrada correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatCarrera'));
    } else {
        $flash = array("title" => "OK", "msg" => "La Carrera esta relacionada, no se permite la eliminación.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatCarrera'));
    }
}) -> name('borrar-carrera');

#XXX Formas de Titulación
$app -> get('/admin/catalogos/formastitulacion/', function() use ($app) {
    $data['formas_titulacion'] = Formas_titulacion::all();
    $app -> render('formas_titulacion.html', $data);
}) -> name('CatFormaTitulacion');

$app -> post('/nueva-titulacion/', function() use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $titulacion = new FormaTitulacion();
        $titulacion -> nombre = $_POST['nombre'];
        $titulacion -> save();
        $flash = array("title" => "OK", "msg" => "La Forma de Titulación se agregó satisfactoriamente .", "type" => "success", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatFormaTitulacion'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatFormaTitulacion'));
    }
}) -> name('nueva-titulacion-post');

$app -> post('/actualiza-titulacion/:id/', function($id) use ($app) {
    $validator = new GUMP();
    $_POST = $validator -> sanitize($_POST);
    $rules = array('nombre-edit' => 'required|max_len,50|min_len,1', );
    $filters = array('nombre-edit' => 'trim|sanitize_string', );
    $post = $_POST = $validator -> filter($_POST, $filters);
    $validated = $validator -> validate($_POST, $rules);
    if ($validated === TRUE) {
        $titulacion = FormaTitulacion::find($id);
        $titulacion -> nombre = $_POST['nombre-edit'];
        $titulacion -> save();
        $flash = array("title" => "OK", "msg" => "Datos de la Forma de Titulación han sido actualizados correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatFormaTitulacion'));
    } else {
        $msgs = humanize_gump($validated);
        $flash = array("title" => "ERROR", "msg" => $msgs, "type" => "error", "fade" => 0);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatFormaTitulacion'));
    }
}) -> name('actualiza-titulacion-post');

//
$app -> get('/borrar-titulacion/:id/', function($id) use ($app) {
    $relaciones = Academico::find_all_by_forma_titulacion($id);
    $cant_relaciones = count($relaciones);
    if ($cant_relaciones == 0) {
        $titulacion = FormaTitulacion::find($id);
        $titulacion -> delete();
        $flash = array("title" => "OK", "msg" => "La Forma de Titulación ha sido borrada correctamente.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatFormaTitulacion'));
    } else {
        $flash = array("title" => "OK", "msg" => "La Forma de Titulación esta relacionado, no se permite la eliminación.", "type" => "info", "fade" => 1);
        $app -> flash("flash", $flash);
        $app -> flashKeep();
        $app -> redirect($app -> urlFor('CatFormaTitulacion'));
    }
}) -> name('borrar-titulacion');
?>
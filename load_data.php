<?php
$app->get('/load/roles/', function() use($app){
    $roles = array('Aceptado','Administrador','Alumno','Aspirante futuro','Aspirante','Desertor','Docente','Ex-alumno','Media manager','No aceptado','No aspirante');
    foreach ($roles as $rol) {
        $r = Rol::find_by_nombre($rol);
        if(!is_object($r)){
            $r = new Rol();
            $r->nombre = $rol;
            $r->save();
            unset($r);
        }
    }
})->name("load-roles");
?>
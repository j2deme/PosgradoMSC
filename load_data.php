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

$app->get('/load/promep-sni/', function() use($app){
    $promeps = array("No","En trámite","Si");
    $snis = array(1,2,3);
    foreach ($promeps as $promep) {
        $p = PROMEP::find_by_nombre($promep);
        if(!is_object($p)){
            $p = new PROMEP();
            $p->nombre = $promep;
            $p->save();
            unset($p);
        }
    }

    foreach ($snis as $sni) {
        $s = SNI::find_by_nombre($sni);
        if(!is_object($s)){
            $s = new SNI();
            $s->nombre = $sni;
            $s->save();
            unset($s);
        }
    }
})->name("load-promep-sni");

$app->get('/load/sections/', function() use($app){
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
})->name("load-sections");
?>
<?php
$app->map("/admin/elFinder/", function() use ($app) {
    $opts = array(
        'roots' => array(
            array(
                'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
                'path'          => './uploads/',         // path to files (REQUIRED)
                'URL'           => $app->urlFor('home'). '/uploads/', // URL to files (REQUIRED)
                'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
            )
        )
    );
    $connector = new elFinderConnector(new elFinder($opts));
    $connector->run();
})->via('GET', 'POST')->name('elFinder');

$app->get('/explorador-archivos/', function() use ($app) {
    $data['user'] = isAllowed("Verificador",false);
    $dir = ($dir == "/uploads/") ? $dir : urldecode($dir);
    $dir = "/uploads/";
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

$app->post('/upload/:type/:id', function ($type, $id) use ($app){
    $tb = new Toolbox();
    if($type == 'section'){
        $post = Seccion::find($id);
        $upload_path = './uploads/sections/';
        $redirectTo = $app->urlFor('editor-seccion',array('slug' => $post->slug)); 
    } else {
        $post = Noticia::find($id);
        $upload_path = './uploads/news/';
        $redirectTo = $app->urlFor('editor-noticia',array('id' => $post->id));
    }
    $not_allowed_filetypes = array('.exe','.bat','.jar');
    $max_filesize = 5242880; // 5MB 
    $num_files = count($_FILES['files']['name']);
    $file = $_FILES;
    $ok = $warnings = 0;
    $msg = "<ul>";
    
    $contenido = (array) json_decode($post->contenido);
    $currentFiles = (array) $contenido['files'];
    $extraFiles = array();            
    for($i = 0; $i < $num_files; $i++){
        $name = $file['files']['name'][$i];
        $ext = substr($name, strpos($name,'.'), strlen($name) - 1);
        $tmp_name = $file['files']['tmp_name'][$i];
        $filesize = filesize($tmp_name);
            
        if(in_array($ext,$not_allowed_filetypes)){
            $msg .= "<li>No se permiten archivos tipo $ext.</li>";
            $warnings++;
        }
        if($filesize > $max_filesize){
            $msg .= "<li>El archivo $name es demasiado grande.</li>";
            $warnings++;
        }
        if(!is_writable($upload_path)){
            break;
        }
        $filename = preg_replace("/[^A-Z0-9._-]/i", "_", $name);
        $uploaded_file = $upload_path.$filename;
        $parts = pathinfo($filename);
        $x = count(glob($uploaded_file));
        $x = ($x == 1) ? $x : $x++;
        if($x >= 1){
            $filename = $parts["filename"]."-$x.".$parts["extension"];
        }
        $uploaded_file = $upload_path.$filename;
        $img_exts = array('png','jpg','gif','jpeg','bmp');
        $isImg = (in_array($ext, $img_exts)) ? true : false;
        if(move_uploaded_file($tmp_name, $uploaded_file)){
            @chmod($uploaded_file, 0777);
            if($isImg){
                $layer = new PHPImageWorkshop\ImageWorkshop(array('imageFromPath' => $uploaded_file));
                $layer->cropMaximumInPixel(0, 0, "MM");
                $layer->resizeInPixel(200, 200);
                $layer->save($upload_path, "thumb_$filename", true, null, 95);
                @chmod($upload_path."thumb_$filename", 0777);
            }
            $msg .= "<li>Archivo $name subido.</li>";
            $ok++;
            $extraFiles[] = $filename;
        } else {
            $msg .= "<li>Ha sucedido algo inesperado. Intentalo nuevamente.</li>";
            $warnings++;
        }   
    }
    $msg .= "</ul>";
    $newCurrent = array();
    foreach ($currentFiles as $file) {
        $newCurrent[] = $file;
    }
    foreach ($extraFiles as $file) {
        $newCurrent[] = $file;
    }
    
    $contenido['files'] = $newCurrent;
    $post->contenido = json_encode($contenido);
    $post->save();
    if ($i < $num_files) {
        $title = "ERROR";
        $msg = "<li>Verifica los permisos del directorio $upload_path. (CHMOD 777)</li>";
        $type = "error";
        $fade = 0;
    } elseif ($warnings >= $ok) {
        $title = "ATENCIÓN";
        $type = "warning";
        $fade = 0;
    } else {
        $title = "OK";
        $type = "success";
        $fade = 1;
    }
    $flash = array("title" => $title,"msg" => $msg,"type" => $type,"fade" => $fade);
    $app -> flash("flash", $flash);
    $app->flashKeep();
    $app->redirect($redirectTo);
})->name('upload-file');

$app->get('/delete-file/:type/:id/:name', function($type, $id, $name) use ($app){
    if($type == 'section'){
        $post = Seccion::find($id);
        $upload_path = './uploads/sections/';
        $redirectTo = $app->urlFor('editor-seccion',array('slug' => $post->slug)); 
    } else {
        $post = Noticia::find($id);
        $upload_path = './uploads/news/';
        $redirectTo = $app->urlFor('editor-noticia',array('id' => $post->id));
    }
    $fileToRemove = $upload_path.$name;
    $thumb = $upload_path."thumb_$name";
    $ext = pathinfo($fileToRemove, PATHINFO_EXTENSION);
    $img_exts = array('png','jpg','gif','jpeg','bmp');
    $isImg = (in_array($ext, $img_exts)) ? true : false;
    if (file_exists($fileToRemove)) {
        if (@unlink($fileToRemove) === true) {
            if($isImg){
                @unlink($thumb);
            }
            $msg = "Archivo $name borrado.";
            $ok = true;
            $contenido = (array) json_decode($post->contenido);
            $currentFiles = $contenido['files'];
            $currentFiles = array_diff($currentFiles, array($name));
            $contenido['files'] = $currentFiles;
            $post->contenido = json_encode($contenido);
            $post->save();
        } else {
            $msg = "No se pudo borrar el archivo $name.";
            $ok = false;
        }
    } else {
        //En esta situación,no es necesario informar que hubo un problema.
        //$msg = "El archivo $name no existe, por lo tanto no se puede borrar.";
        $msg = "Archivo $name borrado.";
        $ok = false;
    }
    if($ok){
        $title = "OK";
        $type = "success";
        $fade = 1;
    } else {
        $title = "¡Atención!";
        $type = "warning";
        $fade = 0;
    }
    $flash = array("title" => $title,"msg" => $msg,"type" => $type,"fade" => $fade);
    $app -> flash("flash", $flash);
    $app->flashKeep();
    $app->redirect($redirectTo);
})->name('delete-file'); 
?>
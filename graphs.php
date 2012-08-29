<?php
$app->get('/admin/estadisticas/', function() use($app){
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Estadísticas", "alias" => "admin-estadisticas")
	);
	$data['user'] = isAllowed("Administrador", false);	
	$app->render('statistics.html',$data);
})->name('admin-estadisticas');

#TODO Matriculacion Anual
$app->get('/graficas/matriculacion-anual/(:slug)', function($slug) use($app){
	//SETTINGS
	$forgotte = __DIR__."/vendor/pChart/fonts/Forgotte.ttf";
	$pfarma5 = __DIR__."/vendor/pChart/fonts/pf_arma_five.ttf";
	$calibri = __DIR__."/vendor/pChart/fonts/calibri.ttf";
 	$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>true,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>true,"CycleBackground"=>true);
	$titleSize = 20;
	$axisSize = 8;
	$m = new Generic();
	$m->iw = 800;//Image Width 800
	$m->ih = 280;//Image Height 280
	$m->rw = $m->iw - 1;//Rectangle Width
	$m->rh = $m->ih - 1;//Rectangle Height
	$m->gx = 60;//Graph X
	$m->gy = 40;//Graph Y
	$m->gw = $m->iw - 50;//Graph Width
	$m->gh = $m->ih - 30;//Graph Height
	$m->lx = 540;//Legend X
	$m->ly = 20;//Legend Y
	$m->tx = 150;//Title X
	$m->ty = 35;//Title Y
	$matriculacion = Matriculacion::all();
	$years = array();
	$aspirantes = array();
	$aceptados = array();
	$colorAspirante = array("R"=>150,"G"=>166,"B"=>28,"Alpha"=>100);
	$colorAceptado = array("R"=>67,"G"=>115,"B"=>18,"Alpha"=>100);
	$faker = Faker\Factory::create();
	foreach ($matriculacion as $mat) {
		$years[] = $mat->periodo;
		$aspirantes[] = $mat->aspirantes;
		$aceptados[] = $mat->aceptados;
		unset($mat);
	}
	$data = new pData();
 	$data->addPoints($aspirantes,"Aspirantes");
 	$data->addPoints($aceptados,"Aceptados");
	$data->setPalette("Aspirantes",$colorAspirante);
	$data->setPalette("Aceptados",$colorAceptado);
 	//$data->setAxisName(0,"Matriculación");
 	$data->addPoints($years,"Labels");
 	$data->setSerieDescription("Labels","Años");
 	$data->setAbscissa("Labels");
	
 	$graph = new pImage($m->iw,$m->ih,$data);
 	$graph->Antialias = true; 
 	$graph->setFontProperties(array("FontName"=>$forgotte,"FontSize"=>11));
 	$graph->drawText($m->tx,$m->ty,"Matriculación Anual",array("FontSize"=>$titleSize,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>$axisSize));
 	$graph->setGraphArea($m->gx,$m->gy,$m->gw,$m->gh);
	$graph->drawScale($scaleSettings);
 	$graph->drawAreaChart(array("DisplayValues"=>true,"DisplayColor"=>DISPLAY_AUTO,"Gradient"=>true));
//	$graph->setFontProperties(array("FontName"=>$pfarma5,"FontSize"=>$axisSize));
 	$graph->drawLegend($m->lx,$m->ly,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
	$graph->drawLineChart();
	$graph->drawPlotChart(array("PlotBorder"=>true,"PlotSize"=>1,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80)); 
 	$graph->stroke();
})->name('g-matriculacion');

#TODO Estadistica Anual de Genero por roles 
$app->get('/graficas/estadistica-genero/(:slug)', function($slug) use($app){
	//Settings
	$forgotte = __DIR__."/vendor/pChart/fonts/Forgotte.ttf";
	$calibri = __DIR__."/vendor/pChart/fonts/calibri.ttf";
	$scaleSettings = array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>true,"CycleBackground"=>true);
	$titleSize = 20;
	$axisSize = 8;
	$m = new Generic();
	$m->iw = 800;//Image Width
	$m->ih = 280;//Image Height
	$m->rw = $m->iw - 1;//Rectangle Width
	$m->rh = $m->ih - 1;//Rectangle Height
	$m->gx = 60;//Graph X
	$m->gy = 40;//Graph Y
	$m->gw = $m->iw - 50;//Graph Width
	$m->gh = $m->ih - 30;//Graph Height
	$m->lx = 300;//Legend X
	$m->ly = 20;//Legend Y
	$m->tx = 150;//Title X
	$m->ty = 35;//Title Y
	$faker = Faker\Factory::create();
	$years = $candidatos = $candidatas = $aceptados = $aceptadas = $alumnos = $alumnas = $exalumnos = $exalumnas = $desertores = $desertoras = array();
	$generoST = GeneroST::all();
	foreach ($$generoST as $g) {
		$years[] = $g->periodo;
		$candidatos[] = $g->candidatos;
		$candidatas[] = $g->candidatas;
		$aceptados[] = $g->aceptados;
		$aceptadas[] = $g->aceptadas;
		$alumnos[] = $g->alumnos;
		$alumnas[] = $g->alumnas;
		$exalumnos[] = $g->exalumnos;
		$exalumnas[] = $g->exalumnas;
	}
	for ($i=2011; $i <= 2015; $i++) { 
		$years[] = $i;
		$candidatos[] = abs($faker->randomDigit);
		$candidatas[] = abs($faker->randomDigit);
		$aceptados[] = abs($faker->randomDigit);
		$aceptadas[] = abs($faker->randomDigit);
		$alumnos[] = abs($faker->randomDigit);
		$alumnas[] = abs($faker->randomDigit);
		$exalumnos[] = abs($faker->randomDigit);
		$exalumnas[] = abs($faker->randomDigit);
	}
	$data = new pData();
	$data->addPoints($candidatos,"Candidatos");
	$data->addPoints($candidatas,"Candidatas");
	$data->addPoints($aceptados,"Aceptados");
	$data->addPoints($aceptadas,"Aceptadas");
	$data->addPoints($alumnos,"Alumnos");
	$data->addPoints($alumnas,"Alumnas");
	$data->addPoints($exalumnos,"Egresados");
	$data->addPoints($exalumnas,"Egresadas");
	$pink = array("R"=>250,"G"=>54,"B"=>100,"Alpha"=>100);
//	$pink = array("R"=>225,"G"=>35,"B"=>35,"Alpha"=>100);
	$blue = array("R"=>41,"G"=>138,"B"=>238,"Alpha"=>100);
//	$data->setPalette(array('Candidatos','Aceptados','Alumnos','Egresados'),$blue);
//	$data->setPalette(array('Candidatas','Aceptadas','Alumnas','Egresadas'),$pink);
//	$data->setAxisName(0,"Relación por Genero");
	$data->addPoints($years,"Labels");
	$data->setSerieDescription("Labels","Años");
	$data->setAbscissa("Labels");
	$data->setAxisDisplay(0,AXIS_FORMAT_CUSTOM,"YAxisFormat");
	
	$graph = new pImage($m->iw, $m->ih, $data);
	$graph->Antialias = true;
 	//$picture->drawRectangle(0,0,$m['rw'],$m['rh'],array("R"=>0,"G"=>0,"B"=>0));
	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>8));
	$graph->setGraphArea($m->gx, $m->gy, $m->gw, $m->gh);
//	$graph->drawScale(array("Floating"=>true,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>true,"CycleBackground"=>true,"Mode"=>SCALE_MODE_ADDALL));
	$graph->drawScale($scaleSettings);
	$graph->setShadow(false);
	$graph->drawBarChart(array("DisplayValues"=>true,"DisplayColor"=>DISPLAY_AUTO,"Gradient"=>false,"Surrounding"=>10,"InnerSurrounding"=>10,"RecordImageMap"=>TRUE));
//	$graph->writeLabel(array("Hombres","Mujeres"),1,array("DrawVerticalLine"=>TRUE));
	$graph->drawLegend($m->lx, $m->ly,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
	$graph->stroke();
})->name('g-genero');

#TODO Estadistica de Genero por roles en año específico
$app->get('/graficas/estadistica-genero-rol/(:year)', function($year) use($app){
	//Settings
	$forgotte = __DIR__."/vendor/pChart/fonts/Forgotte.ttf";
	$calibri = __DIR__."/vendor/pChart/fonts/calibri.ttf";
	$titleSize = 20;
	$axisSize = 8;
	$m = new Generic();
	$m->iw = 800;//Image Width
	$m->ih = 280;//Image Height
	$m->rw = $m->iw - 1;//Rectangle Width
	$m->rh = $m->ih - 1;//Rectangle Height
	$m->gx = 60;//Graph X
	$m->gy = 40;//Graph Y
	$m->gw = $m->iw - 50;//Graph Width
	$m->gh = $m->ih - 30;//Graph Height
	$m->lx = 540;//Legend X
	$m->ly = 20;//Legend Y
	$m->tx = 150;//Title X
	$m->ty = 35;//Title Y
	$faker = Faker\Factory::create();
	$roles = array('Aspirantes','Aceptados','Alumnos','Egresados');
	$hombres = $mujeres = $years = $candidatos = $candidatas = $aceptados = $aceptadas = $alumnos = $alumnas = $exalumnos = $exalumnas = $desertores = $desertoras = array();
	$generoST = GeneroST::find_by_periodo($year);
	$hombres = array($g->candidatos,$g->aceptados,$g->alumnos,$g->egresados);
	$mujeres = array(-($g->candidatas),-($g->aceptadas),-($g->alumnas),-($g->egresadas));
/*	foreach ($roles as $rol) {
		$hombres[] = $faker->randomDigit;
		$mujeres[] = -($faker->randomDigit);
	}*/
	$data = new pData();
	$data->addPoints($hombres,"Hombres");
	$data->addPoints($mujeres,"Mujeres");
	
	$pink = array("R"=>250,"G"=>54,"B"=>100,"Alpha"=>100);
	$blue = array("R"=>41,"G"=>138,"B"=>238,"Alpha"=>100);
	$data->setPalette('Hombres',$blue);
	$data->setPalette('Mujeres',$pink);
	$data->addPoints($roles,"roles");
	$data->setSerieDescription("roles","Roles");
	$data->setAbscissa("roles");
	$data->setAxisDisplay(0,AXIS_FORMAT_CUSTOM,"YAxisFormat");
	
	$graph = new pImage($m->iw, $m->ih, $data);
	$graph->Antialias = true;
 	$graph->setFontProperties(array("FontName"=>$forgotte,"FontSize"=>11));
 	$graph->drawText($m->tx,$m->ty,"Distribución por Genero($year)",array("FontSize"=>$titleSize,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>8));
	$graph->setGraphArea($m->gx, $m->gy, $m->gw, $m->gh);
	$graph->drawScale(array("Floating"=>true,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>true,"CycleBackground"=>true,"Mode"=>SCALE_MODE_ADDALL));
	$graph->setShadow(false);
	$graph->drawStackedBarChart(array("DisplayValues"=>true,"DisplayColor"=>DISPLAY_AUTO,"Gradient"=>true,"Surrounding"=>10,"InnerSurrounding"=>10));
//	$graph->writeLabel(array("Hombres","Mujeres"),1,array("DrawVerticalLine"=>TRUE));
	$graph->drawLegend($m->lx, $m->ly,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
	$graph->stroke();
})->name('g-genero-rol-anual');

$app->get('/graficas/procedencia/(:slug)', function($slug) use($app){
	//Settings
	$forgotte = __DIR__."/vendor/pChart/fonts/Forgotte.ttf";
	$calibri = __DIR__."/vendor/pChart/fonts/calibri.ttf";
	$titleSize = 20;
	$axisSize = 8;
	$m = new Generic();
	$m->iw = 800;//Image Width
	$m->ih = 280;//Image Height
	$m->rw = $m->iw - 1;//Rectangle Width
	$m->rh = $m->ih - 1;//Rectangle Height
	$m->gx = 400;//Graph X
	$m->gy = 140;//Graph Y
	$m->gw = $m->iw - 50;//Graph Width
	$m->gh = $m->ih - 30;//Graph Height
	$m->lx = 550;//Legend X
	$m->ly = 20;//Legend Y
	$m->tx = 150;//Title X
	$m->ty = 35;//Title Y
	$faker = Faker\Factory::create();
	$users = Usuario::all(array('include'=>array('personal')));
	$ids = array();
/*	foreach ($users as $u) {
		$proc = $u->personal->procedencia;
		if(isset($ids[$proc])){
			$ids[$proc]++;
		} else {
			$ids[$proc] = 1;
		}
	}*/
/*	for ($i=1; $i <= 10; $i++) { 
		$proc = abs($faker->randomDigit);
		if(isset($ids[$proc])){
			$ids[$proc]++;
		} else {
			$ids[$proc] = 1;
		}
	}*/
	sort($ids);
/*	$count = count($ids);
	$names = array();
	for ($i=0; $i < $count; $i++) { 
		$names[] = $faker->country();
	}*/
	
	$data = new pData();
	$data->addPoints($ids,"Procedencia");
	$data->setSerieDescription("Procedencia","Procedencia de los aspirantes");
	//$data->addPoints(array("A","B","C","D","E","F","G"),"Labels");
	$data->addPoints($names,"Labels");
 	$data->setAbscissa("Labels"); 
	
	$pink = array("R"=>250,"G"=>54,"B"=>100,"Alpha"=>100);
	$blue = array("R"=>41,"G"=>138,"B"=>238,"Alpha"=>100);
	$data->setPalette('Hombres',$blue);
	$data->setPalette('Mujeres',$pink);
		
	$graph = new pImage($m->iw, $m->ih, $data,true);
	$graph->Antialias = true;
 	$graph->setFontProperties(array("FontName"=>$forgotte,"FontSize"=>11));
 	$graph->drawText($m->tx,$m->ty,"Procedencia de los Aspirantes",array("FontSize"=>$titleSize,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));	
	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>12));
 	$graph->setShadow(true,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20)); 
 	
 	$pie = new pPie($graph,$data);
// 	$pie->setSliceColor(0,array("R"=>143,"G"=>197,"B"=>0));
// 	$pie->setSliceColor(1,array("R"=>97,"G"=>77,"B"=>63));
// 	$pie->setSliceColor(2,array("R"=>97,"G"=>113,"B"=>63));
//	$pie->draw3DPie($m->gx,$m->gy,array("WriteValues"=>TRUE,"DataGapAngle"=>5,"DataGapRadius"=>5,"Border"=>TRUE));
	$pie->draw3DPie($m->gx,$m->gy,array("Radius"=>125,"WriteValues"=>true,"LabelStacked"=>TRUE,"DataGapAngle"=>5,"DataGapRadius"=>5,"Border"=>TRUE));
//	$graph->setGraphArea($m->gx, $m->gy, $m->gw, $m->gh);
//	$graph->drawScale(array("Floating"=>true,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>true,"CycleBackground"=>true,"Mode"=>SCALE_MODE_ADDALL));
//	$graph->setShadow(false);
//	$graph->drawStackedBarChart(array("DisplayValues"=>true,"DisplayColor"=>DISPLAY_AUTO,"Gradient"=>true,"Surrounding"=>10,"InnerSurrounding"=>10));
	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>8));
	$pie->drawPieLegend($m->lx, $m->ly,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_VERTICAL));
//	$graph->drawLegend($m->lx, $m->ly,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
	$graph->stroke(); 
})->name('g-procedencia');

function YAxisFormat($Value) { return(abs($Value)); }
?>
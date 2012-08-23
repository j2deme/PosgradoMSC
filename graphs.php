<?php
$app->get('/admin/estadisticas/', function() use($app){
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Estadísticas", "alias" => "admin-estadisticas")
	);
	$data['user'] = isAllowed("Administrador", false);	
	$app->render('statistics.html',$data);
})->name('admin-estadisticas');

$app->get('/graficas/matriculacion-anual/(:slug)', function($slug) use($app){
	//SETTINGS
	$forgotte = __DIR__."/vendor/pChart/fonts/Forgotte.ttf";
	$calibri = __DIR__."/vendor/pChart/fonts/calibri.ttf";
 	$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>true,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>true,"CycleBackground"=>true);
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
/*	for ($i=2011; $i <= 2020; $i++) { 
		$years[] = $i;
		$aspirantes[] = $acept = abs($faker->randomDigit+2);
		$aceptados[] = abs($faker->randomDigit - $acept);
	}*/
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
// 	$graph->drawRectangle(0,0,$m->rw,$m->rh,array("R"=>0,"G"=>0,"B"=>0)); 
 	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>11));
// 	$graph->drawText($m->tx,$m->ty,"Matriculación Anual",array("FontSize"=>$titleSize,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>$axisSize));
 	$graph->setGraphArea($m->gx,$m->gy,$m->gw,$m->gh);
	$graph->drawScale($scaleSettings);
 	$graph->drawAreaChart(array("DisplayValues"=>true,"DisplayColor"=>DISPLAY_AUTO,"Gradient"=>true));
	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>$axisSize));
 	$graph->drawLegend($m->lx,$m->ly,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
	$graph->drawLineChart();
	$graph->drawPlotChart(array("PlotBorder"=>true,"PlotSize"=>1,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80)); 
 	$graph->stroke();
})->name('grafica-matriculacion');

$app->get('/graficas/estadistica-genero/(:slug)', function($slug) use($app){
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
	$years = array();
	$aspirantes = array();
	$aceptados = array();
	$faker = Faker\Factory::create();
	$years = $hombres = $mujeres = array();
	for ($i=2011; $i <= 2020; $i++) { 
		$years[] = $i;
		$hombres[] = $faker->randomDigit;
		$mujeres[] = -($faker->randomDigit);
	}
	$data = new pData();
	$data->addPoints($hombres,"Hombres");
	$data->addPoints($mujeres,"Mujeres");
	$pink = array("R"=>250,"G"=>54,"B"=>100,"Alpha"=>100);
//	$pink = array("R"=>225,"G"=>35,"B"=>35,"Alpha"=>100);
	$blue = array("R"=>41,"G"=>138,"B"=>238,"Alpha"=>100);
	$data->setPalette('Hombres',$blue);
	$data->setPalette('Mujeres',$pink);
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
	$graph->drawScale(array("Floating"=>true,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>true,"CycleBackground"=>true,"Mode"=>SCALE_MODE_ADDALL));
	$graph->setShadow(false);
	$graph->drawStackedBarChart(array("DisplayValues"=>true,"DisplayColor"=>DISPLAY_AUTO,"Gradient"=>true,"Surrounding"=>10,"InnerSurrounding"=>10));
//	$graph->writeLabel(array("Hombres","Mujeres"),1,array("DrawVerticalLine"=>TRUE));
	$graph->drawLegend($m->lx, $m->ly,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
	$graph->stroke();
})->name('grafica-genero');

function YAxisFormat($Value) { return(abs($Value)); }
?>
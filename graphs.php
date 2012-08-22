<?php
$app->get('/admin/estadisticas/', function() use($app){
	$data['breadcrumb'] = array(
		array("name" => "Panel de Control","alias" => "admin"),
		array("name" => "Estadísticas", "alias" => "admin-estadisticas")
	);
	$data['user'] = isAllowed("Administrador", false);	
	$app->render('statistics.html',$data);
})->name('admin-estadisticas');

$app->get('/graficas/matriculacion-anual/(:x)', function($x) use($app){
	//SETTINGS
	$forgotte = __DIR__."/vendor/pChart/fonts/Forgotte.ttf";
	$calibri = __DIR__."/vendor/pChart/fonts/calibri.ttf";
 	$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>true,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>true,"CycleBackground"=>true);
	$x = (!isset($x))? 1: $x;
	$titleSize = 20 * $x;
	$axisSize = 8 * $x;
	$m = array(
		'iw' => 800 * $x, //Image Width
		'ih' => 260 * $x, //Image Height
		'rw' => 799 * $x, //Rectangle Width
		'rh' => 259 * $x, //Rectangle Height
		'gx' => 60 * $x, //Graph X
		'gy' => 40 * $x, //Graph Y
		'gw' => 750 * $x, //Graph Width
		'gh' => 230 * $x, //Graph Height
		'lx' => 540 * $x, //Legend X
		'ly' => 20 * $x, //Legend Y
		'tx' => 150 * $x, //Title X
		'ty' => 35 * $x, //Title Y
	);
	$matriculacion = Matriculacion::all();
	$years = array();
	$aspirantes = array();
	$aceptados = array();
	$faker = Faker\Factory::create();
/*	foreach ($matriculacion as $mat) {
		$years[] = $mat->periodo;
		$aspirantes[] = $mat->aspirantes;
		$aceptados[] = $mat->aceptados;
		unset($mat);
	}*/
	for ($i=2011; $i <= 2020; $i++) { 
		$years[] = $i;
		$aspirantes[] = $faker->randomDigit;
		$aceptados[] = $faker->randomDigit;
	}
	$data = new pData();
 	$data->addPoints($aspirantes,"Aspirantes");
 	$data->addPoints($aceptados,"Aceptados");
 	$data->setAxisName(0,"Matriculación");
 	$data->addPoints($years,"Labels");
 	$data->setSerieDescription("Labels","Años");
 	$data->setAbscissa("Labels");
 	$graph = new pImage($m['iw'],$m['ih'],$data);
 	$graph->Antialias = true;
 	$graph->drawRectangle(0,0,$m['rw'],$m['rh'],array("R"=>0,"G"=>0,"B"=>0)); 
 	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>11));
 	$graph->drawText($m['tx'],$m['ty'],"Matriculación Anual",array("FontSize"=>$titleSize,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>$axisSize));
 	$graph->setGraphArea($m['gx'],$m['gy'],$m['gw'],$m['gh']);
	$graph->drawScale($scaleSettings);
 	$graph->drawAreaChart(array("DisplayValues"=>true,"DisplayColor"=>DISPLAY_AUTO));
	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>$axisSize));
 	$graph->drawLegend($m['lx'],$m['ly'],array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
// 	$graph->Antialias = true;
// 	$graph->drawAreaChart();
	$graph->setShadow(true,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
	$graph->drawLineChart();
	$graph->drawPlotChart(array("PlotBorder"=>true,"PlotSize"=>1,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80)); 
 	$graph->stroke();
})->name('grafica-matriculacion');

$app->get('/graficas/estadistica-genero/(:x)', function($x) use($app){
	//Settings
	$forgotte = __DIR__."/vendor/pChart/fonts/Forgotte.ttf";
	$calibri = __DIR__."/vendor/pChart/fonts/calibri.ttf";
 	$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>true,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>true,"CycleBackground"=>true);
	$x = (!isset($x))? 1: $x;
	$titleSize = 20 * $x;
	$axisSize = 8 * $x;
	$m = array(
		'iw' => 800 * $x, //Image Width
		'ih' => 260 * $x, //Image Height
		'rw' => 799 * $x, //Rectangle Width
		'rh' => 259 * $x, //Rectangle Height
		'gx' => 60 * $x, //Graph X
		'gy' => 40 * $x, //Graph Y
		'gw' => 750 * $x, //Graph Width
		'gh' => 230 * $x, //Graph Height
		'lx' => 540 * $x, //Legend X
		'ly' => 20 * $x, //Legend Y
		'tx' => 150 * $x, //Title X
		'ty' => 35 * $x, //Title Y
	);
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
	$blue = array("R"=>41,"G"=>138,"B"=>238,"Alpha"=>100);
	$data->setPalette('Hombres',$blue);
	$data->setPalette('Mujeres',$pink);
	$data->setAxisName(0,"Relación por Genero");
	$data->addPoints($years,"Labels");
	$data->setSerieDescription("Labels","Años");
	$data->setAbscissa("Labels");
	$data->setAxisDisplay(0,AXIS_FORMAT_CUSTOM,"YAxisFormat");
	
	/* Create the pChart object */
	$picture = new pImage($m['iw'],$m['ih'],$data);
	$picture->Antialias = true;
 	$picture->drawRectangle(0,0,$m['rw'],$m['rh'],array("R"=>0,"G"=>0,"B"=>0));
	//$picture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100));
	//$picture->drawGradientArea(0,0,700,230,DIRECTION_HORIZONTAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>20));
	
	/* Set the default font properties */
	$picture->setFontProperties(array("FontName"=>$calibri,"FontSize"=>8));
	/* Draw the scale and the chart */
	$picture->setGraphArea($m['gx'],$m['gy'],$m['gw'],$m['gh']);
//	$picture->drawScale($scaleSettings);
	$picture->drawScale(array("Floating"=>true,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>true,"CycleBackground"=>true,"Mode"=>SCALE_MODE_ADDALL));
	$picture->setShadow(false);
	$picture->drawStackedBarChart(array("DisplayValues"=>true,"DisplayColor"=>DISPLAY_AUTO,"Gradient"=>true,"Surrounding"=>10,"InnerSurrounding"=>10));
	
	/* Write the chart legend */
	$picture->drawLegend(600,210,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
	
	/* Render the picture (choose the best way) */
	$picture->autoOutput("pictures/example.drawStackedBarChart.pyramid.png");
	
	function YAxisFormat($Value) { return(abs($Value)); }
})->name('grafica-genero');
?>
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
	foreach ($matriculacion as $mat) {
		$years[] = $mat->periodo;
		$aspirantes[] = $mat->aspirantes;
		$aceptados[] = $mat->aceptados;
		unset($mat);
	}
	$data = new pData();
 	$data->addPoints($aspirantes,"Aspirantes");
 	$data->addPoints($aceptados,"Aceptados");
 	$data->setAxisName(0,"Matriculación");
 	$data->addPoints($years,"Labels");
 	$data->setSerieDescription("Labels","Años");
 	$data->setAbscissa("Labels");
 	$graph = new pImage($m['iw'],$m['ih'],$data);
 	$graph->Antialias = false;
 	$graph->drawRectangle(0,0,$m['rw'],$m['rh'],array("R"=>0,"G"=>0,"B"=>0)); 
 	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>11));
 	$graph->drawText($m['tx'],$m['ty'],"Matriculación Anual",array("FontSize"=>$titleSize,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>$axisSize));
 	$graph->setGraphArea($m['gx'],$m['gy'],$m['gw'],$m['gh']);
	$graph->drawScale($scaleSettings);
 	$graph->drawAreaChart(array("DisplayValues"=>true,"DisplayColor"=>DISPLAY_AUTO));
	$graph->setFontProperties(array("FontName"=>$calibri,"FontSize"=>$axisSize+2));
 	$graph->drawLegend($m['lx'],$m['ly'],array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
// 	$graph->Antialias = true;
// 	$graph->drawAreaChart();
	$graph->setShadow(true,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
	$graph->drawLineChart();
	$graph->drawPlotChart(array("PlotBorder"=>true,"PlotSize"=>1,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80)); 
 	$graph->stroke();
})->name('grafica-matriculacion');

$app->get('/graficas/matriculacion-anual/(:x)', function($x) use($app){
	
});
?>
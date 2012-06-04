<?php
require '../vendor/ActiveRecord/ActiveRecord.php';
$connections = array(
	'development' => 'mysql://root:root@localhost/taskmanager',
	'production' => 'mysql://'.getenv('MYSQL_USERNAME').':'.getenv('MYSQL_PASSWORD').'@'.getenv('MYSQL_DB_HOST').'/'.getenv('MYSQL_DB_NAME').';charset=utf8'
);
ActiveRecord\Config::initialize(function($cfg) use ($connections){
	$cfg->set_model_directory('../models');
	$cfg->set_connections($connections); 
	$cfg->set_default_connection('production');
});
?>
<style>
sub,
sup {
  position: relative;
  font-size: 75%;
  line-height: 0;
  vertical-align: baseline;
}
sup {
  top: -0.5em;
}
sub {
  bottom: -0.25em;
}
img {
  max-width: 100%;
  vertical-align: middle;
  border: 0;
  -ms-interpolation-mode: bicubic;
}
body {
  margin: 0;
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 13px;
  line-height: 18px;
  color: #333333;
  background-color: #ffffff;
}
a {
  color: #0088cc;
  text-decoration: none;
}
p {
  margin: 0 0 9px;
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 13px;
  line-height: 18px;
}
p small {
  font-size: 11px;
  color: #999999;
}
h1,
h2,
h3,
h4,
h5,
h6 {
  margin: 0;
  font-family: Helvetica, Arial, sans-serif;
  font-weight: bold;
  color: #333333;
  text-rendering: optimizelegibility;
}
h1 small,
h2 small,
h3 small,
h4 small,
h5 small,
h6 small {
  font-weight: normal;
  color: #999999;
}
h1 {
  font-size: 30px;
  line-height: 36px;
  padding-bottom: 17px;
  margin: 18px 0;
  border-bottom: 1px solid #eeeeee;
}
h1 small {
  font-size: 18px;
}
h2 {
  font-size: 24px;
  line-height: 36px;
}
h2 small {
  font-size: 18px;
}
h3 {
  font-size: 18px;
  line-height: 27px;
}
h3 small {
  font-size: 14px;
}
h4,
h5,
h6 {
  line-height: 18px;
}
h4 {
  font-size: 14px;
}
h4 small {
  font-size: 12px;
}
h5 {
  font-size: 12px;
}
h6 {
  font-size: 11px;
  color: #999999;
  /*text-transform: uppercase;*/
}
ul,
ol {
  padding: 0;
  margin: 0 0 9px 25px;
}
ul ul,
ul ol,
ol ol,
ol ul {
  margin-bottom: 0;
}
ul {
  list-style: disc;
}
ol {
  list-style: decimal;
}
li {
  line-height: 18px;
}
ul.unstyled,
ol.unstyled {
  margin-left: 0;
  list-style: none;
}
hr {
  margin: 18px 0;
  border: 0;
  border-top: 1px solid #eeeeee;
  border-bottom: 1px solid #ffffff;
}
strong {
  font-weight: bold;
}
em {
  font-style: italic;
}
small {
  font-size: 100%;
}
table {
  max-width: 130mm;
  background-color: transparent;
  border-collapse: collapse;
  border-spacing: 0;
}
.table {
  width: 130mm;
  margin-bottom: 18px;
}
.table th,
.table td {
  padding: 8px;
  line-height: 18px;
  text-align: left;
  vertical-align: top;
  /*border-top: 1px solid #dddddd;*/
}
.table th {
  font-weight: bold;
  text-align: center;
}
.table thead th {
  vertical-align: bottom;
}
.table tbody + tbody {
  /*border-top: 2px solid #dddddd;*/
}
.table-condensed th,
.table-condensed td {
  padding: 4px 5px;
}
.table-bordered {
  /*border: 1px solid #dddddd;*/
  border-width: thin;
  border-collapse: separate;
  border-left: 0;
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  border-radius: 4px;
}
.table-bordered th,
.table-bordered td {
  /*border-left: 1px solid #dddddd;*/
}
.custom {
	border-collapse:collapse;
}
.custom td, .custom th {
font-size:1em;
border:1px solid #242424;
}
.custom th 
{
font-size:1.1em;
text-align:center;
background-color:#111;
color:#ffffff;
}
.custom tr.alt td 
{
color:#000000;
background-color:#f3f2f0;
}
</style>
<h1>Reporte de progreso al <?php echo date("d/m/Y",time());?></h1><br/>
<page_footer>
    <table style="width: 100%;">
        <tr>
            <td style="text-align: left; width: 50%"><small>Generado el <?php echo date("d/m/Y \a \l\a\s H:i:s");?></small></td>
            <td style="text-align: right; width: 50%">Página [[page_cu]] de [[page_nb]]</td>
        </tr>
    </table>
</page_footer>
<?php
$districts = District::find('all');
$activities = Activity::find('all');
	foreach ($districts as $district) {
		foreach ($activities as $activity) {
			foreach ($activity->tasks as $task) {
				$assignation = Assignation::find_by_district_and_activity_and_task($district->id,$activity->id,$task->id);
				$subtasks = $assignation->subtasks;
				if(count($subtasks) > 0){
					echo "<page>";
					echo "<h2>".$district->description." <small>".htmlentities($activity->name,ENT_QUOTES)."</small></h2>";
?>
<table class="table table-bordered table-condensed custom">
	<thead>
		<tr>
			<th colspan="5"><?php echo htmlentities($task->name,ENT_QUOTES);?></th>
		</tr>
		<tr>
			<th>Subtarea</th>
			<th>Fecha Inicio</th>
			<th>Fecha Fin</th>
			<th>Estatus</th>
			<th>Historial</th>
		</tr>
	</thead>
	<tbody>
<?php
		$x = 0;
		foreach ($subtasks as $sb) {
			$class = ($x == 1) ? "alt" : "" ;
			$x = ($x == 0) ? 1 : 0 ;
?>
		<tr class="<?php echo $class; ?>">
			<td><?php echo htmlentities($sb->name,ENT_QUOTES); ?></td>
			<td><?php echo date("d/m/Y",$sb->start); ?></td>
			<td><?php echo date("d/m/Y",$sb->end); ?></td>
			<td>
				<?php
				if($sb->deleted == 1){
					echo "Eliminada";
				} else {
					echo status($sb->progress);
				}
				?>
			</td>
			<td>
				<ul class="unstyled">
<?php
foreach ($sb->comments as $comment) {
	echo "<li><strong>".date("d/m/Y H:i:s",$comment->created)." - ".status($comment->status)."</strong><br/>";
	echo htmlentities($comment->body,ENT_QUOTES)."</li>";
}
?>
				</ul>
			</td>
		</tr>
<?php
		}
?>

	</tbody>
</table>
<?php
	echo '
	<page_footer>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left;    width: 50%"><small>Generado el '.date("d/m/Y \a \l\a\s H:m:s").'</small></td>
                <td style="text-align: right;    width: 50%">Página [[page_cu]] de [[page_nb]]</td>
            </tr>
        </table>
    </page_footer>';
	echo "</page>";
				}
			}
		}
	}

function status($progress){
	switch ($progress) {
		case 0:
			return "No iniciada";
			break;
		case 1:
			return "Iniciada";
			break;
		case 2:
			return "Finalizada";
			break;
		default:
			return "No iniciada";
			break;
	}
}
?>
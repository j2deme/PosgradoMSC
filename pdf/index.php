<?php
ob_start();
include('report.php');
$content = ob_get_clean();
//$content = "<h1>Hello World</h1>";
require_once('html2pdf.class.php');
try {
        $html2pdf = new HTML2PDF('L', 'LETTER', 'es',true,'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->writeHTML($content);
        $html2pdf->Output('Reporte.pdf');
} catch(HTML2PDF_exception $e) {
	echo $e;
	exit;
}
?>
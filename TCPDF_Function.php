<?php
function OutputPdf($name,$body){
    
    require_once(dirname(__FILE__).'/tcpdf/config/lang/eng.php');
    require_once(dirname(__FILE__).'/tcpdf/tcpdf.php');
    
    $pdf = new TCPDF();
    
    $pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.'', PDF_HEADER_STRING);
    
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    // ---------------------------------------------------------
    $pdf->AddPage();
    $pdf->setRTL(true);
    
    $pdf->addTTFfont(dirname(__FILE__).'/tcpdf/arial.ttf','TrueTypeUnicode','',32);
    $pdf->SetFont('arial', '', 10);
    
    $htmlcontent = $body;
    
    $pdf->WriteHTML($htmlcontent, true, 0, true, 0);
    
    $pdf->Output($name, 'I');

}

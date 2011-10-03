<?php
if(isset($pdf)){
        $font = Font_Metrics::get_font("arial");
        $size = 10;
        $color = array(0,0,0);
        $text_height = Font_Metrics::get_font_height($font, $size);
        $foot = $pdf->open_object();
        $w = $pdf->get_width();
        $h = $pdf->get_height();
        $y = $h - 2 * $text_height - 24;
        $pdf->line(16, $y, $w - 16, $y, $color, 1);
        $y += $text_height;
        $text = "My Awesome Website! (edit PdfExport_headfoot.php to change this text) | " . date("F j, Y");
        $pdf->text(16, $y, $text, $font, $size, $color);
        $pdf->close_object();
        $pdf->add_object($foot, "all");
        $text = "Page {PAGE_NUM}/{PAGE_COUNT}";
        $width = Font_Metrics::get_text_width($text, $font, $size);
        $pdf->page_text($w-5 - $width, $y, $text, $font, $size, $color);
}
?>

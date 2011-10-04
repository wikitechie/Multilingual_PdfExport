<?php
# This file has been modified by DumpyDooby.
if ( !defined( 'MEDIAWIKI' ) ) 
        die();
 
# <Craig>
$PdfExportUseHtmlDoc = isset($PdfExportUseHtmlDoc) ? $PdfExportUseHtmlDoc : false;
# </Craig>

$wgPdfExportAttach = false; // set to true if you want output as an attachment
$wgPdfExportHttpsImages = false; // set to true if page is on a HTTPS server and contains images that are on the HTTPS server and also 
//                                  reachable with HTTP
 
class SpecialPdf extends SpecialPage {
        var $title;
        var $article;
        var $html;
        var $parserOptions;
        var $bhtml;
        public $iswindows;
 
        function SpecialPdf() {
                global $iswindows;
                SpecialPage::SpecialPage( 'PdfPrint' );
                $os = getenv ("SERVER_SOFTWARE");
                $iswindows = strstr ($os, "Win32");
        }
 
        public function write1file ($text) {
                // make a temporary directory with an unique name
                // NOTE: If no PDF file is created and you get message "ERROR: No HTML files!", 
                //       try using a temporary directory that is within web server space.
                //       For example (assuming the web server root directory is /var/www/html):
                //           $mytemp = "/var/html/www/tmp/f" .time(). "-" .rand() . ".html";
 
 
                # <Craig>
         $mytemp=tempnam(sys_get_temp_dir(), 'PdfExport');
                # </Craig>
         $article_f = fopen($mytemp,'w');
                if($article_f === FALSE){
                        error_log("Failed opening temporary HTML file to \"$mytemp\" failed", 0);
                        return;
                }
                fwrite($article_f, $text);
                # <Craig>
         fseek($article_f, 0);
                # </Craig>
         fclose($article_f);
                return $mytemp;
        }
 
        public function save1page ( $page ) {
                global $wgUser;
                global $wgParser;
                global $wgScriptPath;
                global $wgServer;
                global $wgPdfExportHttpsImages;
 
                $title = Title::newFromText( $page );
                if( is_null( $title ) || !$title->userCanRead() )
                        return null;
                $article = new Article ($title);
                $parserOptions = ParserOptions::newFromUser( $wgUser );
                $parserOptions->setEditSection( false );
                $parserOptions->setTidy(true);
                $wgParser->mShowToc = false;
                $parserOutput = $wgParser->parse( $article->preSaveTransform( $article->getContent() ) ."\n\n", $title, $parserOptions );
 
                $bhtml = $parserOutput->getText();
                // Hack to thread the EUR sign correctly
                $bhtml = str_replace(chr(0xE2) . chr(0x82) . chr(0xAC), chr(0xA4), $bhtml);
                //$bhtml = utf8_decode($bhtml);
 
                $bhtml = str_replace ($wgScriptPath, $wgServer . $wgScriptPath, $bhtml);
                $bhtml = str_replace ('/w/',$wgServer . '/w/', $bhtml);
                if ($wgPdfExportHttpsImages)
                        $bhtm = str_replace('img src=\"https:\/\/','img src=\"http:\/\/', $bhtml);
 
                $html = "<html><head><title>" . utf8_decode($page) . "</title></head><body>" . $bhtml . "</body></html>";
                return $this->write1file ($html);
        }
 
        function outputpdf ($pages, $landscape, $size) {
                global $iswindows;
                global $wgPdfExportAttach;
                $returnStatus = 0;
                $pagestring = "";
                $pagefiles = array();
                $foundone = false;
 
                foreach ($pages as $pg) {
                        $f = $this->save1page ($pg);
                        if ($f == null)   continue;
                        $foundone = true;
                        if ($iswindows) $pagestring .= "\"" . $f . "\" ";
                        else $pagestring .= $f . " ";
                        $pagefiles[] = $f;
                }
                if ($foundone == false)   return;
 
                putenv("HTMLDOC_NOCGI=1");
 
                # Write the content type to the client...
         header("Content-Type: application/pdf");

                if ($wgPdfExportAttach)
                        header(sprintf('Content-Disposition: attachment; filename="%s.pdf"', $page));
                #TODO remove the header lines above 
                # <Craig>
         global $PdfExportUseHtmlDoc;
                if($PdfExportUseHtmlDoc == true && is_executable('htmldoc')){
                        # Run HTMLDOC to provide the PDF file to the user...
                 passthru("htmldoc -t pdf14 --charset iso-8859-15 --color --quiet --jpeg --size " . $size . " " . $landscape . "--webpage " . $pagestring, $returnStatus);
                        if($returnStatus == 1)
                                error_log("Generating PDF failed. Return status was:" . $returnStatus, 0);
                }
                else{
                	// USING TCPDF lib
                        $PdfContent = '';
                        foreach ($pagefiles as &$pagefile){
                                $PdfContent .= file_get_contents($pagefile);
                                #TODO should we put splitter <hr>
                        }
                        // force utf-8
                        $PdfContent = str_replace('<head>','<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>',$PdfContent);
                        $PdfContent = str_replace('</head>','<style type="text/css">body{padding:10px 10px 35px;}</style></head>',$PdfContent); // Insert styling to make room for header/footer.
                        $orientation = stristr($landscape,'landscape') ? 'landscape' : 'portrait';
                        
						#TODO include TCPDF library
			include('TCPDF_Function.php');
			OutputPdf('doc.pdf',$PdfContent);
                }
                # </Craig>
         flush();
                foreach ($pagefiles as $pgf) {
                        unlink ($pgf);
                }
        }
 
        function execute( $par ) {
                global $wgRequest;
                global $wgOut; 
 
                wfLoadExtensionMessages ('PdfPrint');
                $dopdf = false;
                if ($wgRequest->wasPosted()) {
                        $pagel = $wgRequest->getText ('pagel');
                        $pages = array_filter( explode( "\n", $pagel ), 'wfFilterPage1' );
                        $size = $wgRequest->getText ('Size', 'Letter');
                        $orientations = $wgRequest->getVal ('orientation');
                        $orientation = ($orientations == 'landscape') ? " --landscape --browserwidth 1200 " : " --portrait ";
                        $dopdf = true;
                }
                else {
                        $page = isset( $par ) ? $par : $wgRequest->getText( 'page' );
                        if ($page != "") $dopdf = true; 
                        $pages = array ($page);
                        $orientation = " --portrait ";
                        $size = "Letter";
                }
                if ($dopdf) {
                        $wgOut->setPrintable();
                        $wgOut->disable();
 
                        $this->outputpdf ($pages, $orientation, $size);
                        return;
                }
 
                $self = SpecialPage::getTitleFor( 'PdfPrint' );
                $wgOut->addHtml( wfMsgExt( 'pdf_print_text', 'parse' ) );
 
                $form = Xml::openElement( 'form', array( 'method' => 'post',
                'action' => $self->getLocalUrl( 'action=submit' ) ) );
 
                $form .= Xml::openElement( 'textarea', array( 'name' => 'pagel', 'cols' => 40, 'rows' => 10 ) );
                $form .= Xml::closeElement( 'textarea' );
                $form .= '<br />';
                $form .= Xml::radioLabel(wfMsg ('pdf_portrait'), 'orientation' , 'portrait' , 'portrait', true);  
                $form .= Xml::radioLabel(wfMsg ('pdf_landscape'), 'orientation' , 'landscape' , 'landscape', false);  
                $form .= '<br />' . wfMsg('pdf_size');
                $form .= Xml::listDropDown ('Size', wfMsg ('pdf_size_options'),'', wfMsg('pdf_size_default'));
                $form .= Xml::submitButton( wfMsg( 'pdf_submit' ) );
                $form .= Xml::closeElement( 'form' );
                $wgOut->addHtml( $form );
 
        }
 
}
 
function wfFilterPage1( $page ) {
        return $page !== '' && $page !== null;
}
?>

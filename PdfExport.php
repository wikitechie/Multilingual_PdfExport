<?php
if ( !defined( 'MEDIAWIKI' ) )
     die ();
 
$wgExtensionCredits['specialpage'][] = array(
        'name' => 'PdfExport',
        'author' => array( 'Thomas Hempel', '...' ),
        'version' => '2.4.2 (2011-02-23)',
        'description' => 'renders a page as pdf',
        'url' => 'http://www.mediawiki.org/wiki/Extension:Pdf_Export'
);
 
$dir = dirname(__FILE__) . '/';
# Internationalisation file
$wgExtensionMessagesFiles['PdfPrint'] = $dir . 'PdfExport.i18n.php';
$wgExtensionAliasesFiles['PdfPrint'] = $dir . 'PdfExport.i18n.alias.php';
$wgSpecialPageGroups['PdfPrint'] = 'pagetools';
 
# Add special page.
$wgSpecialPages['PdfPrint'] = 'SpecialPdf';
$wgAutoloadClasses['SpecialPdf'] = $dir . 'PdfExport_body.php';
 
$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink'][] = 'wfSpecialPdfNav';
$wgHooks['SkinTemplateToolboxEnd'][] = 'wfSpecialPdfToolbox';
 
function wfSpecialPdfNav( &$skintemplate, &$nav_urls, &$oldid, &$revid ) {
        wfLoadExtensionMessages( 'PdfPrint' );
        $nav_urls['pdfprint'] = array(
                        'text' => wfMsg( 'pdf_print_link' ),
                        'href' => $skintemplate->makeSpecialUrl( 'PdfPrint', "page=" . wfUrlencode( "{$skintemplate->thispage}" )  )
                );
        return true;
}
 
function wfSpecialPdfToolbox( &$monobook ) {
        wfLoadExtensionMessages( 'PdfPrint' );
        if ( isset( $monobook->data['nav_urls']['pdfprint'] ) )
                if ( $monobook->data['nav_urls']['pdfprint']['href'] == '' ) {
                        ?><li id="t-ispdf"><?php echo $monobook->msg( 'pdf_print_link' ); ?></li><?php
                } else {
                        ?><li id="t-pdf">
<?php
                                ?><a href="<?php echo htmlspecialchars( $monobook->data['nav_urls']['pdfprint']['href'] ) ?>"><?php
                                        echo $monobook->msg( 'pdf_print_link' );
                                ?></a><?php
                        ?></li><?php
                }
        return true;
}
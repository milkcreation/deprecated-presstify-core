<?php
function tify_pdfviewer_display( $pdf_url, $args = array(), $echo = true )
{
    return \tiFy\Components\PDFViewer\PDFViewer::display( $pdf_url, $args, $echo );
}
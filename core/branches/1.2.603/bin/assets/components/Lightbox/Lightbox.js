jQuery(document).ready( function($){
    // Galeries Wordpress
    $('[id^="gallery-"]').each(function(){
        tiFyImageLightbox($('a', $(this)), tiFyLightbox);
    });
    // Médias des articles 
    tiFyImageLightbox($('a').has('img[class*="wp-image-"]' ), tiFyLightbox);
});
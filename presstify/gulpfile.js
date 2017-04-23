var gulp    = require( 'gulp' ),
    plumber = require( 'gulp-plumber' ),
    csso    = require( 'gulp-csso' ),
    rename  = require( 'gulp-rename' ),
    uglify  = require( 'gulp-uglify' ),
    path    = './vendor/tiFy/Assets/tify';

gulp.task( 'minifyCSS', function() {
    gulp.src( [ path + '/*.css', '!'+ path +'/*.min.css' ] )
        .pipe( plumber() )
        .pipe( csso() )
        .pipe( rename( { extname: '.min.css' } ) )
        .pipe( gulp.dest( path ) );
});

gulp.task( 'minifyJS', function() {
    gulp.src( [ path + '/*.js', '!'+ path +'/*.min.js' ] )
        .pipe( plumber() )
        .pipe( uglify() )
        .pipe( rename( { extname: '.min.js' } ) )
        .pipe( gulp.dest( path ) );
});
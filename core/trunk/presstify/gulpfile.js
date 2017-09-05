var gulp    = require('gulp'),
    plumber = require('gulp-plumber'),
    csso    = require('gulp-csso'),
    rename  = require('gulp-rename'),
    uglify  = require('gulp-uglify'),
    path    = './bin/assets';

gulp.task('minifyCSS', function() {
    gulp.src([ path + '/**/*.css', '!'+ path +'/**/*.min.css', '!'+ path +'/vendor/**/*.css' ])
        .pipe(plumber())
        .pipe(csso())
        .pipe(rename({extname:'.min.css'}))
        .pipe(gulp.dest(function(file) {
                return file.base;
            })
        );
});

gulp.task('minifyJS', function() {
    gulp.src([ path + '/**/*.js', '!'+ path +'/**/*.min.js', '!'+ path +'/vendor/**/*.js' ])
        .pipe(plumber())
        .pipe(uglify())
        .pipe(rename({extname:'.min.js'}))
        .pipe(gulp.dest(function(file) {
                return file.base;
            })
        );
});

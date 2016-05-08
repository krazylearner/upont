var fs             = require('fs');
var gulp           = require('gulp');
var templateCache  = require('gulp-angular-templatecache');
var autoprefixer   = require('gulp-autoprefixer');
var concat         = require('gulp-concat');
var filter         = require('gulp-filter');
var htmlReplace    = require('gulp-html-replace');
var jshint         = require('gulp-jshint');
var less           = require('gulp-less');
var uglify         = require('gulp-uglify');
var uglifycss      = require('gulp-uglifycss');
var gutil          = require('gulp-util');
var mainBowerFiles = require('main-bower-files');
var path           = require('path');

/**
 * Vérifie la syntaxe JS
 */
gulp.task('lint-js', function() {
    var appFiles = [
        'app/js/app.js',
        'app/js/*.js',
        'app/js/**/*.js',
        'app/js/controllers/**/*.js'
    ];
    return gulp.src(appFiles)
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'))
    ;
});

/**
 * Construit le fichier JS
 */
gulp.task('build-js', function() {
    // On doit charger le redactor avant angular-redactor qui est dans les bowerfiles, mais redactor dépend de jquery
    // On exclut donc jquery du main dans le bower.json et on l'introduit manuellement
    var redactor = [
        'www/libs/jquery/dist/jquery.js',
        'www/libs/redactor/redactor.js',
        'www/libs/redactor/table.js',
        'www/libs/redactor/video.js',
        'www/libs/redactor/fr.js'
    ];
    var vendorsFiles = mainBowerFiles();
    var appFiles = [
        'app/js/app.js',
        'app/js/*.js',
        'app/js/**/*.js',
        'app/js/controllers/**/*.js'
    ];
    var files = redactor.concat(vendorsFiles.concat(appFiles));
    return gulp.src(files)
        .pipe(filter(['**/*.js', '**/*.coffee']))
        .pipe(concat('upont.min.js'))
        .pipe(gutil.env.type === 'production' ? uglify() : gutil.noop())
        .pipe(gulp.dest('www/'))
    ;
});

/**
 * Liste les fichiers d'un répertoire
 * @param  {string} dir Le dossier
 * @return {string[]}   La liste des fichiers
 */
function getFiles(dir) {
    return fs
        .readdirSync(dir)
        .filter(function(file) {
            return !fs.statSync(path.join(dir, file)).isDirectory();
        });
}
var themesPath = 'app/css/themes/';

/**
 * Construit le CSS en créeant un fichier CSS par thème
 */
gulp.task('build-css', function() {
    var vendorsFiles = mainBowerFiles();
    var themeFiles;

    if (gutil.env.type == "production") {
        themeFiles = getFiles(themesPath);
    } else {
        themeFiles = ['classic.less', 'classic-dark.less'];
    }


    var tasks = themeFiles.map(function(file) {
        return gulp.src(vendorsFiles.concat([themesPath + file]))
            .pipe(filter(['**/*.css', '**/*.less']))
            .pipe(less())
            .pipe(concat(file.replace(/less/, '') + 'min.css'))
            .pipe(autoprefixer({
                cascade: false
            }))
            .pipe(gutil.env.type === 'production' ? uglifycss() : gutil.noop())
            .pipe(gulp.dest('www/themes/'))
        ;
   });
});

/**
 * Construit le fichier HTML suivant l'environnement
 */
gulp.task('build-html', function(){
    return gulp.src('app/js/index.html')
        .pipe(gutil.env.type == "production" ? htmlReplace({base: '<base href="/">'}) : gutil.noop())
        .pipe(gulp.dest('www/'));
});

/**
 * Récupère les vues, les compile et les met dans le cache Angular
 */
gulp.task('build-templates', function(){
    gulp.src([
        'app/js/*.html',
        'app/js/**/*.html',
    ])
    .pipe(htmlReplace({ponthub: ''}))
    .pipe(templateCache({
        module: 'templates',
        standalone: true
    }))
    .pipe(gulp.dest('./www'));

    gulp.src([
        'app/js/*.html',
        'app/js/**/*.html',
    ])
    .pipe(templateCache('templates-ponts.js', {
        module: 'templates',
        standalone: true
    }))
    .pipe(gulp.dest('./www'));
});

/**
 * Copie les polices des vendors dans le bon dossier
 */
gulp.task('copy-fonts', function () {
    return gulp.src(mainBowerFiles().concat('www/libs/fontawesome/fonts/*'))
        .pipe(filter(['**/*.eot', '**/*.svg', '**/*.ttf', '**/*.woff', '**/*.woff2', '**/*.otf']))
        .pipe(gulp.dest('www/fonts/'));
});

/**
 * Définition du WATCH
 */
gulp.task('watch', function() {
    gulp.watch(['app/js/**/*.js', 'app/js/*.js'], ['lint-js', 'build-js']);
    gulp.watch(['app/js/*.html', 'app/js/**/*.html'], ['build-templates']);
    gulp.watch(['app/css/**/*.less'], ['build-css']);
    gulp.watch(['app/js/index.html'], ['build-html']);
});

/**
 * Définition du BUILD
 */
gulp.task('build', [
    'build-js',
    'build-css',
    'build-html',
    'build-templates',
    'copy-fonts'
]);

/**
 * Tache par défaut
 */
gulp.task('default', ['build', 'watch']);

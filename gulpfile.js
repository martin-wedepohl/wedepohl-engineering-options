const { src, dest, watch, series, parallel } = require("gulp");
const autoprefixer = require("gulp-autoprefixer");
const browserSync = require("browser-sync").create();
const csso = require("gulp-csso");
const del = require("del");
const mode = require("gulp-mode")();
const rename = require("gulp-rename");
const sass = require("gulp-dart-sass");
const sourcemaps = require("gulp-sourcemaps");
const browserify = require("browserify");
const babelify = require("babelify");
const source = require("vinyl-source-stream");
const buffer = require("vinyl-buffer");
const uglify = require("gulp-uglify");

/**
 * Directories and files
 */
const srcBase = "./src";
const srcStylePath = srcBase + "/scss";
const srcScriptPath = srcBase + "/js";

const distBase = "../../wp-content/plugins/wedepohl-engineering-options";
const distStylePath = distBase + "/dist/css";
const distScriptPath = "js/";

const styleFiles = srcStylePath + "/**/*.scss";
const scriptFiles = srcScriptPath + "/**/*.js";
const rootFiles = srcBase + "/**/*.php";
const licenseFile = "./license.txt";
const vendor = srcBase + "/vendor";

const indexJsFile = "/script.js";
const adminJsFile = "/script-admin.js";
const jsFiles = [indexJsFile, adminJsFile];

// clean tasks
const clean = () => del([distBase], {force: true});

// copy tasks
const copyRoot = () => {
    src(rootFiles)
		.pipe(dest(distBase));
	src(vendor)
		.pipe(dest(distBase));
	return src(licenseFile)
		.pipe(dest(distBase));
    // return src(templateFiles)
    //     .pipe(dest(distTemplatePath));
}

const copyIndex = () => {
    return src(srcBase + "/index.php")
        .pipe(dest(distBase + "/dist"))
        .pipe(dest(distBase + "/dist/css"))
        .pipe(dest(distBase + "/dist/js"))
}

// css task
const css = () => {
    return src(styleFiles)
        .pipe(mode.development(sourcemaps.init({loadMaps: true})))
        .pipe(sass().on("error", sass.logError))
        .pipe(autoprefixer())
        .pipe(
            rename(({ dirname, basename, extname }) => {
                return {
                    dirname,
                    basename: `${basename}.min`,
                    extname
                }
            })
        )
        .pipe(mode.production(csso()))
        .pipe(mode.development(sourcemaps.write('.')))
        .pipe(dest(distStylePath))
        .pipe(browserSync.reload({
            stream: true
        }));
}

// js task
const js = (done) => {
    jsFiles.map( entry => {
        return browserify({
            entries: [srcScriptPath + entry]
        })
        .transform( babelify, {"presets": ['@babel/preset-env']})
        .bundle()
        .pipe( source( entry ) )
        .pipe(
            rename(({ basename, extname }) => {
                return {
                    dirname: distScriptPath,
                    basename: `${basename}.min`,
                    extname
                }
            })
        )
        .pipe( buffer() )
        .pipe( mode.development( sourcemaps.init( {loadMaps: true} ) ) )
        .pipe( mode.production( uglify() ) )
        .pipe(mode.development( sourcemaps.write('.', {includeContent: false, sourceRoot: '.' } ) ) )
        .pipe(dest(distBase + "/dist"));
    });
    done();
}

// watch task
const watchForChanges = () => {

    browserSync.init({
        proxy: {
            target: "http://spyglass-hitek.local"
        }
    });

    watch(styleFiles, css);
    watch(scriptFiles, js).on("change", browserSync.reload);
    watch(rootFiles, copyRoot).on("change", browserSync.reload);

    return console.log("Gulp is watching ....");
}

// public tasks
exports.clean = clean
exports.copyRoot = copyRoot;
exports.copyIndex = copyIndex;
exports.css = css
exports.js = js
exports.build = series(clean, copyRoot, copyIndex, parallel(css, js));
exports.default = series(clean, copyRoot, copyIndex, parallel(css, js), watchForChanges);
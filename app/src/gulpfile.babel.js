import concat from 'gulp-concat';
import gulp from 'gulp';
import gulpif from 'gulp-if';
import livereload from 'gulp-livereload';
import merge from 'merge-stream';
import order from 'gulp-order';
import uglify from 'gulp-uglify';
import uglifycss from 'gulp-uglifycss';
import upath from 'upath';
import yargs from 'yargs';

const { argv } = yargs
  .options({
    rootPath: {
      description: '<path> path to web assets directory',
      type: 'string',
      requiresArg: true,
      required: false,
    },
    nodeModulesPath: {
      description: '<path> path to node_modules directory',
      type: 'string',
      requiresArg: true,
      required: false,
    },
    srcPath: {
      description: '<path> path to src directory',
      type: 'string',
      requiresArg: true,
      required: false,
    },
  });

const env = process.env.GULP_ENV;
const srcPath = upath.normalizeSafe(argv.srcPath);
const rootPath = '../public/assets';
const ApiRootPath = upath.joinSafe(rootPath, 'api');
const ShopRootPath = upath.joinSafe(rootPath, 'shop');
const datepickerRootPath = upath.joinSafe(rootPath, 'datepicker');
const BundlePath = upath.joinSafe(srcPath, './');

const paths = {
  api: {
    css: [
      upath.joinSafe(BundlePath, 'Resources/private/css/main.css')
    ]
  },
  shop: {
    css: [
      upath.joinSafe(BundlePath, 'Resources/private/css/shop.override.css')
    ]
  },
  datepicker: {
    css: [
      upath.joinSafe(BundlePath, 'Resources/private/datepicker/datepicker.css')
    ],
    js: [
      upath.joinSafe(BundlePath, 'Resources/private/datepicker/datepicker.js'),
      upath.joinSafe(BundlePath, 'Resources/private/datepicker/i18n/datepicker.fr.js')
    ]
  }
};

export const buildShopImg = function buildShopImg() {
  return gulp.src(upath.joinSafe(BundlePath, 'Resources/private/img/*'))
    .pipe(gulp.dest(upath.joinSafe(ShopRootPath, 'img')));
};
buildShopImg.description = 'Build Shop IMG assets.';

export const buildShopCSS = function buildShopCSS() {
  const copyStream = merge(
    gulp.src(upath.joinSafe(BundlePath, 'Resources/private/css/shop.override.css'))
      .pipe(gulp.dest(upath.joinSafe(ShopRootPath, 'css'))),
  );

  const cssStream = gulp.src(paths.shop.css, { base: './' })
    .pipe(concat('css-files.css'));

  return merge(
    copyStream,
    merge(cssStream)
      .pipe(order(['css-files.css']))
      .pipe(concat('override.css'))
      .pipe(gulpif(env === 'prod', uglifycss()))
      .pipe(gulp.dest(upath.joinSafe(ShopRootPath, 'css')))
      .pipe(livereload()),
  );
};
buildShopCSS.description = 'Build Shop css assets.';

export const buildApiCss = function buildApiCss() {
  const copyStream = merge(
    gulp.src(upath.joinSafe(BundlePath, 'Resources/private/css/main.css'))
      .pipe(gulp.dest(upath.joinSafe(ApiRootPath, 'css'))),
  );

  const cssStream = gulp.src(paths.api.css, { base: './' })
    .pipe(concat('css-files.css'));

  return merge(
    copyStream,
    merge(cssStream)
      .pipe(order(['css-files.css']))
      .pipe(concat('main.css'))
      .pipe(gulpif(env === 'prod', uglifycss()))
      .pipe(gulp.dest(upath.joinSafe(ApiRootPath, 'css')))
      .pipe(livereload()),
  );
};
buildApiCss.description = 'Build Api css assets.';

export const buildDatepickerCss = function buildDatepickerCss() {
  const copyStream = merge(
    gulp.src(upath.joinSafe(BundlePath, 'Resources/private/datepicker/datepicker.css'))
      .pipe(gulp.dest(upath.joinSafe(datepickerRootPath, 'css'))),
  );

  const cssStream = gulp.src(paths.datepicker.css, { base: './' })
    .pipe(concat('css-files.css'));

  return merge(
    copyStream,
    merge(cssStream)
      .pipe(order(['css-files.css']))
      .pipe(concat('datepicker.css'))
      .pipe(gulpif(env === 'prod', uglifycss()))
      .pipe(gulp.dest(upath.joinSafe(datepickerRootPath, 'css')))
      .pipe(livereload()),
  );
};
buildDatepickerCss.description = 'Build Datepicker css assets.';

export const buildDatepickerjs = function buildDatepickerjs() {
  const copyStream = merge(
    gulp.src(upath.joinSafe(BundlePath, 'Resources/private/datepicker/datepicker.js'))
      .pipe(gulp.dest(upath.joinSafe(datepickerRootPath, 'js'))),
  );

  const jsStream = gulp.src(paths.datepicker.js, { base: './' })
    .pipe(concat('js-files.js'));

  return merge(
    copyStream,
    merge(jsStream)
      .pipe(order(['js-files.js']))
      .pipe(concat('datepicker.js'))
      .pipe(gulpif(env === 'prod', uglify()))
      .pipe(gulp.dest(upath.joinSafe(datepickerRootPath, 'js')))
      .pipe(livereload()),
  );
};
buildDatepickerjs.description = 'Build Datepicker js assets.';

export const buildDatepickerLocale = function buildDatepickerLocale() {
  const copyStream = merge(
    gulp.src(upath.joinSafe(BundlePath, 'Resources/private/datepicker/i18n/datepicker.fr.js'))
      .pipe(gulp.dest(upath.joinSafe(datepickerRootPath, 'js'))),
  );

  const jsStream = gulp.src(paths.datepicker.js, { base: './' })
    .pipe(concat('js-files.js'));

  return merge(
    copyStream,
    merge(jsStream)
      .pipe(order(['js-files.js']))
      .pipe(concat('datepicker.fr.js'))
      .pipe(gulpif(env === 'prod', uglify()))
      .pipe(gulp.dest(upath.joinSafe(datepickerRootPath, 'js')))
      .pipe(livereload()),
  );
};
buildDatepickerLocale.description = 'Build Datepicker locale assets.';

export const buildApiFont = function buildApiFont() {
  return gulp.src(upath.joinSafe(BundlePath, 'Resources/private/fonts/Gill_Sans_Std_Light.woff2'))
    .pipe(gulp.dest(upath.joinSafe(ApiRootPath, 'fonts')));
};
buildApiFont.description = 'Generate web font package from woof2 files.';

export const watchApi = function watchApi() {
  livereload.listen();

  gulp.watch(paths.api.css, buildApiCss);
};
watchApi.description = 'Watch Api asset sources and rebuild on changes.';

export const watchDatepicker = function watchDatepicker() {
  livereload.listen();

  gulp.watch(paths.datepicker.css, buildDatepickerCss);
};
watchDatepicker.description = 'Watch Datepicker asset sources and rebuild on changes.';

export const build = gulp.parallel(buildShopImg, buildShopCSS, buildApiCss, buildApiFont, buildDatepickerCss, buildDatepickerjs, buildDatepickerLocale);
build.description = 'Build assets.';

export const watch = gulp.parallel(build, watchApi, watchDatepicker);
watch.description = 'Watch asset sources and rebuild on changes.';

gulp.task('shop-img', buildShopImg);
gulp.task('shop-css', buildShopCSS);
gulp.task('api-css', buildApiCss);
gulp.task('api-font', buildApiFont);
gulp.task('api-watch', watchApi);
gulp.task('datepicker-css', buildDatepickerCss);
gulp.task('datepicker-js', buildDatepickerjs);
gulp.task('datepicker-locale', buildDatepickerLocale);
gulp.task('datepicker-watch', watchDatepicker);

export default build;

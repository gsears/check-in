/*
webpack.config.js
Gareth Sears - 2493194S

Webpack configuration for building frontend components.
*/

var path = require("path");
var Encore = require("@symfony/webpack-encore");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath("public/build/")
  // public path used by the web server to access the output path
  .setPublicPath("/build")

  // set the entry points
  // the JS created in these files are accessed in twig templates using the 'encore_entry_script_tags()'
  // function. any CSS created in these files can be accessed using the 'encore_entry_link_tags()' function.
  // the first argument is the alias, the second is the entrypoint file.

  // This imports the main application javascript used on all pages
  .addEntry("app", "./assets/js/app.js")

  // This imports the main application javascript that needs to be included in the <head> tags.
  // (namely a function which intercepts inline <script> tags and loads them at the bottom of <body>)
  .addEntry("preload", "./assets/js/preload.js")

  // This loads the VueLoader for using .vue files.
  .enableVueLoader()

  // When enabled, Webpack "splits" the files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // will require an extra script tag for runtime.js
  .enableSingleRuntimeChunk()

  // Other config options. See:
  // https://symfony.com/doc/current/frontend.html#adding-more-features
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  // enables @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = "usage";
    config.corejs = 3;
  })

  // enables Sass/SCSS support
  .enableSassLoader();

var webpackConfig = Encore.getWebpackConfig();

module.exports = webpackConfig;

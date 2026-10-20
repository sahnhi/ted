const Encore = require("@symfony/webpack-encore");

// Admin plugin config
Encore.setOutputPath("public/build/admin/")
  .setPublicPath("/build/admin")
  .addEntry("admin-entry", "./assets/admin/entry.js")
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .autoProvidejQuery()
  .enableSassLoader();

const adminConfig = Encore.getWebpackConfig();

adminConfig.externals = Object.assign({}, adminConfig.externals, {
  window: "window",
  document: "document",
});
adminConfig.name = "admin";

module.exports = [adminConfig];

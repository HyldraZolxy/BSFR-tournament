const TerserPlugin = require("terser-webpack-plugin");

module.exports = {
    mode: "production",
    context: __dirname + "/www/js",
    entry: "./init.js",
    optimization: {
        minimize: true,
        minimizer: [
            new TerserPlugin({
                parallel: true
            })
        ],
    },
};
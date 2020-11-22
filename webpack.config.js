const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = (env, options) => {
  const devMode = options.mode !== "production";

  return {
    entry: "./resources/js/main.js",
    output: {
      filename: "bundle.js",
      path: path.resolve(__dirname, "public"),
    },
    plugins: [new MiniCssExtractPlugin({ filename: "bundle.css" })],
    module: {
      rules: [
        {
          test: /\.s[ac]ss$/i,
          use: [
            // fallback to style-loader in development
            MiniCssExtractPlugin.loader,
            "css-loader",
            "sass-loader",
          ],
        },
      ],
    },
  };
};

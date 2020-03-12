const path = require("path");
const common = require("./webpack.common");
const merge = require("webpack-merge");
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCssAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const HtmlWebpackPlugin = require("html-webpack-plugin");

module.exports = merge(common, {
    mode: "production",
    output: {
        publicPath: '/dist/'
    },
    plugins: [
        new MiniCssExtractPlugin({ filename: "[name].[contentHash].css" }),
        new CleanWebpackPlugin(),
        new HtmlWebpackPlugin({
            template: "./src/main.html",
            filename: "../index.html",
            minify: {
                removeAttributeQuotes: true,
                collapseWhitespace: true,
                removeComments: true
            }
        })
    ],
    optimization: {
        minimizer: [
            new OptimizeCssAssetsPlugin(),
            new TerserPlugin()
        ]
    },
    module: {
        rules: [
            {
              test: /\.css$/,
              use: [
                  MiniCssExtractPlugin.loader,
                  "css-loader",
              ]
            },
            {
                test: /\.(svg|png|jpe?g|gif)$/,
                use: {
                    loader: "file-loader",
                    options: {
                        name: "[name].[hash].[ext]",
                        outputPath: '/imgs',
                        esModule: false,
                    }
                }
            }
        ]
    }
});

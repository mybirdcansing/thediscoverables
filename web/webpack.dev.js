const path = require("path");
const common = require("./webpack.common");
const merge = require("webpack-merge");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const CopyPlugin = require('copy-webpack-plugin');

module.exports =  merge(common, {
    mode: "development",
    devServer: {
        proxy: {
            '/lib/handlers': 'http://[::1]'
        },
        contentBase: "./dist",
        historyApiFallback: true
    },
    output: {
        publicPath: '/'
    },
    plugins: [
        new CopyPlugin([
            { from: 'favicon.ico'},
        ]),
        new HtmlWebpackPlugin({
            template: "./src/main.html",
            filename: "./index.html"
        })
    ],
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    'vue-style-loader',
                    "css-loader"
                ]
            },
            {
                test: /\.(svg|png|jpe?g|gif)$/,
                use: {
                    loader: "file-loader",
                    options: {
                        name: "[name].[hash].[ext]",
                        outputPath: './imgs',
                        esModule: false,
                    }
                }
            }
        ]
    }
});

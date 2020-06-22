const common = require("./webpack.common");
const webpack = require("webpack");
const merge = require("webpack-merge");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const CopyPlugin = require('copy-webpack-plugin');

module.exports =  merge(common, {
    mode: "development",
    devServer: {
        proxy: {
            '/lib/handlers': 'http://127.0.0.1:8000',
            '/artwork': 'http://127.0.0.1:8000',
            '/audio': 'http://127.0.0.1:8000',
        },
        contentBase: "./dist",
        historyApiFallback: true
    },
    output: {
        publicPath: '/'
    },
    plugins: [
        new CopyPlugin([
            { from: 'favicon.ico' },
        ]),
        new HtmlWebpackPlugin({
            template: "./src/main.html",
            filename: "./index.html"
        }),
        new webpack.EnvironmentPlugin({
            NODE_ENV: 'development', // use 'development' unless process.env.NODE_ENV is defined
            DEBUG: false
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

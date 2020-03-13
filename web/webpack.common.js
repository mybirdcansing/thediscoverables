const path = require("path");
const webpack = require("webpack");
const VueLoaderPlugin = require('vue-loader/lib/plugin')

module.exports = {
    entry: {
        main: "./src/main.js",
    },
    plugins: [
        new webpack.ProvidePlugin({
            Vue: ['vue/dist/vue.esm.js', 'default'],
        }),
        new VueLoaderPlugin()
    ],
    output: {
        filename: "[name].[contentHash].bundle.js",
        chunkFilename: '[name].[contentHash].bundle.js',
        path: path.resolve(__dirname, "dist"),
    },
    devtool: 'source-map',
    module: {
        rules: [
            {
                test: /\.m?js$/,
                exclude: /(node_modules|bower_components)/,
                use: [{
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env'],
                        plugins: ["@babel/plugin-proposal-class-properties"],
                        plugins: ["@babel/plugin-proposal-private-methods"],
                        plugins: ["@babel/plugin-syntax-dynamic-import"]
                    }
                },
                // {
                //     loader:  'webpack-conditional-loader'
                // }
                ]
            },
            {
                test: /\.html/,
                use: ["html-loader"]
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            }
        ]
    },
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js'
        }
    }
};

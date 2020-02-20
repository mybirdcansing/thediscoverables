const path = require("path");
const webpack = require("webpack");

module.exports = {
    entry: {
        main: "./src/index.js",
        vendor: "./src/vendor.js"
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery",
            ko: "knockout"
        })
    ],
    output: {
        filename: "[name].[contentHash].bundle.js",
        path: path.resolve(__dirname, "dist")
    },
    module: {
        rules: [
            {
                test: /\.m?js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env'],
                        plugins: ["@babel/plugin-proposal-class-properties"]
                    }
                }
            },
            {
                test: /\.html/,
                use: ["html-loader"]
            },
            {
                test: /\.(svg|png|jpe?g|gif)$/,
                use: {
                    loader: "file-loader",
                    options: {
                        name: "[name].[hash].[ext]",
                        outputPath:  (url, resourcePath, context) => {
                            const relativePath = path.relative(context, resourcePath);
                            if (/admin-assets/.test(relativePath)) {
                                return `admin/imgs/${url}`;
                            }
                            return `imgs/${url}`;
                        },
                        publicPath: 'imgs',
                        esModule: false,
                    }
                }
            }
        ]
    }
};

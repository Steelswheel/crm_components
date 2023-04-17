const path = require('path');

module.exports = {
    runtimeCompiler: true,
    productionSourceMap: false,
    css: {
        sourceMap: true
    },
    publicPath: '/local/components/vaganov/dist',
    assetsDir: '',
    outputDir: 'dist',
    pages: {
        main: {
            entry: './main.js',
            template: 'public/main.html',
            filename: 'main.html',
        },
    },
    chainWebpack: config => {
        config.plugins.delete('preload-test_component')
    },
    configureWebpack: {
        output: {
            filename: process.env.NODE_ENV === 'production'
                ? '[name].[contenthash].bundle.js'
                : '[name].bundle.js',
        },
        optimization: {
            splitChunks: false
        },
        resolve: {
            /*modules: [
                path.resolve(__dirname, 'admin/js')
            ],*/
            alias: {
                '@assets': path.join(__dirname, "assets"),
                '@testComponent': path.join(__dirname, "test.component/templates/.default"),
                '@contactShow': path.join(__dirname, "edp.show/templates/.default"),
                '@app': path.join(__dirname, "app-js"),
            }
        }
    },
    devServer: {
        host: 'crm.local',
        proxy: {
            "/": {
                target: "http://crm.local"
                // secure: false
            }
        }
    }
}
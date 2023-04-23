// Node
const path = require('path');

// Webpack
const webpack = require("webpack");
const {merge} = require("webpack-merge");

// Other
const devMode = process.env.NODE_ENV !== "production";

// Vue
const VUE_VERSION = require("vue/package.json").version;
const VUE_LOADER_VERSION = require("vue-loader/package.json").version;

// Webpack plugins
const TerserPlugin = require("terser-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const {VueLoaderPlugin} = require("vue-loader");
const VuetifyLoaderPlugin = require('vuetify-loader/lib/plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

// Webpack abilities
const WEBPACK_DEV_SERVER_HOST = process.env.WEBPACK_DEV_SERVER_HOST || 'localhost';
const WEBPACK_DEV_SERVER_PORT = parseInt(process.env.WEBPACK_DEV_SERVER_PORT, 10) || 8080;
const WEBPACK_DEV_SERVER_PROXY_HOST = process.env.WEBPACK_DEV_SERVER_PROXY_HOST || 'localhost';
const WEBPACK_DEV_SERVER_PROXY_PORT = parseInt(process.env.WEBPACK_DEV_SERVER_PROXY_PORT, 10) || 8000;

// Config
const ROOT_PATH = __dirname;
const CACHE_PATH = ROOT_PATH + "/temp/webpack";

var AssetsPlugin = require('assets-webpack-plugin');

module.exports = {
  mode: devMode ? "development" : "production",
  context: path.join(ROOT_PATH, "app/assets"),
  entry: {
    front: path.join(ROOT_PATH, "app/assets/js/main.js"),
  },
  output: {
    path: path.join(ROOT_PATH, 'www/dist'),
    publicPath: "",
    filename: devMode ? '[name].bundle.js' : '[name].[chunkhash:8].bundle.js',
    clean: true,
    //chunkFilename: devMode ? '[name].chunk.js' : '[name].[chunkhash:8].chunk.js'
  },
  devtool: 'cheap-module-source-map',
  module: {
		noParse: /^(vue|vue-router|vuex|vuex-router-sync)$/,
		rules: [
			{
				test: /\.vue$/,
				use: [
					{
						loader: 'vue-loader',
						options: {
							compilerOptions: {
								preserveWhitespace: false
							},
						}
					}
				]
			},
			{
				test: /\.js$/,
				exclude: file => (
					/node_modules/.test(file) &&
					!/\.vue\.js/.test(file)
				),
				use: [
					{
						loader: 'babel-loader',
					}
				]
			},
			/*{
				test: /\.tsx?$/,
				exclude: /node_modules/,
				use: [{
					loader: 'awesome-typescript-loader',
				}
				]
			},*/
			{
				test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/i,
				use: [
					{
						loader: 'url-loader',
						options: {
							limit: 4096,
							fallback: {
								loader: 'file-loader',
								options: {
									name: 'fonts/[name].[hash:8].[ext]'
								}
							}
						}
					}
				]
			},
			{
				test: /\.(svg)(\?.*)?$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: 'imgs/[name].[hash:8].[ext]'
						}
					}
				]
			},
			{
				test: /\.(png|jpe?g|gif|webp|ico)(\?.*)?$/,
				use: [
					{
						loader: 'url-loader',
						options: {
							limit: 4096,
							fallback: {
								loader: 'file-loader',
								options: {
									name: 'imgs/[name].[hash:8].[ext]'
								}
							}
						}
					}
				]
			},
			{
				test: /\.(css|scss)$/,
				use: [
					MiniCssExtractPlugin.loader,
					{
						loader: 'css-loader',
						options: {
							sourceMap: false,
							importLoaders: 2,
							modules: false
						}
					},
					{
						loader: "postcss-loader",
						options: {
							postcssOptions: {
								ident: "postcss",
								plugins: [require("autoprefixer")]
							}
						}
					},
          {
            loader: 'sass-loader',
            //options: {
              // This is the path to your variables
              //additionalData: "@import '@/admin/css/scss/variables.scss';"
            //},
          },
				],
			},
      {
				test: /\.sass$/,
				use: [
					MiniCssExtractPlugin.loader,
					{
						loader: 'css-loader',
						options: {
							sourceMap: false,
							importLoaders: 2,
							modules: false
						}
					},
					{
						loader: "postcss-loader",
						options: {
							postcssOptions: {
								ident: "postcss",
								plugins: [require("autoprefixer")]
							}
						}
					},
          {
            loader: 'sass-loader',
            options: {
              // This is the path to your variables
              additionalData: "@import '@/admin/css/scss/variables.scss'"
            },
          },
				],
			},
		]
	}, 
  resolve: {
    alias: {
        'vue$': 'vue/dist/vue.esm.js',
        '@': path.resolve(ROOT_PATH, 'app/assets'),
    },
    extensions: ['.js', '.vue']
  },
  plugins: [
    // enable vue-loader to use existing loader rules for other module types
		new VueLoaderPlugin(),

    new VuetifyLoaderPlugin(),
    
    // fix legacy jQuery plugins which depend on globals
		new webpack.ProvidePlugin({
			$: "jquery",
			jQuery: "jquery",
			"window.jQuery": "jquery",
			"window.$": "jquery",
      Popper: ["popper.js", "default"],
      naja: ['naja', 'default'],  // https://forum.nette.org/cs/25444-ublaboo-datagrid-mocny-rychly-rozsiritelny-hezky-anglicky-dokumentovany-datagrid?p=36#p213906
		}),
    
    new MiniCssExtractPlugin({
      filename: devMode ? '[name].bundle.css' : '[name].[chunkhash:8].bundle.css'
    }),
    new AssetsPlugin({ // Pre aplikaciu filename: '[name].[contenthash:8].[ext]' a prepojenie s nette
      //includeManifest: 'manifest',
      path: path.join(ROOT_PATH, 'www/dist')
    })
  ],
  //devtool: 'cheap-module-source-map',
  performance: {
    hints: false
  }
};


// ****************************
// WEBPACK DEVELOPMENT CONFIG *
// ****************************

if (process.env.NODE_ENV === 'development') {
  const development = {
    devServer: {
      host: WEBPACK_DEV_SERVER_HOST,
      port: WEBPACK_DEV_SERVER_PORT,
      //disableHostCheck: true,
      //contentBase: path.join(ROOT_PATH, 'www'),
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': '*'
      },
      //stats: 'errors-only',
      hot: true,
      //inline: true,
      proxy: {
        '/': `http://${WEBPACK_DEV_SERVER_PROXY_HOST}:${WEBPACK_DEV_SERVER_PROXY_PORT}`
      }
    }
  };

  module.exports = merge(module.exports, development);
}


// ***************************
// WEBPACK PRODUCTION CONFIG *
// ***************************

if (process.env.NODE_ENV === 'production') {
  const production = {
    devtool: 'source-map',
    optimization: {
      minimizer: [
        new TerserPlugin({
					test: /\.m?js(\?.*)?$/i,
				}),
        new CssMinimizerPlugin(),
      ],
      minimize: true,
    },
  };

  module.exports = merge(module.exports, production);
}


// ************************
// WEBPACK OPT-INS CONFIG *
// ************************

if (process.env.WEBPACK_REPORT === '1') {
  module.exports.plugins.push(
    new BundleAnalyzerPlugin({
      analyzerMode: 'server',
      openAnalyzer: true,
    })
  );
}
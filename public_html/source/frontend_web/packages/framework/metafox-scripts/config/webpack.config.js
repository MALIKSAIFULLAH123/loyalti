const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const PnpWebpackPlugin = require('pnp-webpack-plugin');
const webpack = require('webpack');
const { Workbox } = require('../workbox');
const getStyleLoaders = require('./utils/getStyleLoaders');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const GenerateJsonPlugin = require('generate-json-webpack-plugin');

const log = require('../helpers/log');
const ESLintWebpackPlugin = require('eslint-webpack-plugin');
// does not need in webpack v5
const TerserPlugin = require('terser-webpack-plugin');
const paths = require('./paths');
const InterpolateHtmlPlugin = require('react-dev-utils/InterpolateHtmlPlugin');
const BundleAnalyzerPlugin =
  require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
require('../config/env');
const includeEnv = require('./includeEnv');
const includeEnvDef = Object.keys(includeEnv).reduce((acc, key) => {
  acc[`process.env.${key}`] = JSON.stringify(includeEnv[key]);
  return acc;
}, {});

// enable logs
const DEBUG = true;
const KB = 1024;
const WorkboxRootDir = Workbox.getRootDir();

// https://www.npmjs.com/package/eslint-webpack-plugin
const eslintConfig = {
  cwd: WorkboxRootDir,
  errorOnUnmatchedPattern: true,
  useEslintrc: true,
  extensions: ['ts', 'tsx'],
  lintDirtyModulesOnly: true,
  emitError: true,
  emitWarning: true,
  failOnError: true,
  failOnWarning: false,
  quiet: false,
  outputReport: false
};

const CaseSensitivePathsPlugin = require('case-sensitive-paths-webpack-plugin');

const MomentLocalesPlugin = require('moment-locales-webpack-plugin');
const imageInlineSizeLimit = parseInt(
  process.env.IMAGE_INLINE_SIZE_LIMIT || '10000'
);

module.exports = function createWebpackConfig(opts) {
  const manifest = require(paths.appSettingJson);

  const {
    proxy,
    host,
    port,
    allowedHosts,
    disableFirewall,
    webpackEnv,
    bundle
  } = opts;

  const isBuildService = !!process.env.MFOX_BUILD_SERVICE;
  const isProd = 'production' === webpackEnv;
  const isDev = !isProd;
  const sourceMap = false;

  log.heading('WEBPACK BUILD SETTINGS ' + process.env.MFOX_BUILD_TYPE);

  Object.keys(includeEnv).forEach(key => {
    if (key.match(/^(MFOX_|NODE_|BABEL_|PUBLIC_)/))
      log.info(key + ':', includeEnv[key]);
  });
  return {
    // https://webpack.js.org/configuration/cache/#cache
    // disable webpack build cache
    cache: isDev,
    target: ['browserslist'],
    mode: webpackEnv,
    // performance: isDev,
    entry: {
      index: {
        import: paths.appIndexJs
        // dependOn: ['reactjs']
      }
      // reactjs: ReactJsGroup
    },
    output: {
      filename:
        isProd || process.env.analyzer
          ? 'static/js/[name].[contenthash:8].js'
          : 'static/js/bundle.[contenthash:8].js',
      chunkFilename:
        isProd && !process.env.analyzer
          ? 'static/js/[chunkhash:8].chunk.js'
          : 'static/js/[name].chunk.js',
      assetModuleFilename: 'static/media/[name].[contenthash:8][ext]',
      path: paths.appBuild,
      publicPath: paths.publicPath,
      // publicPath: 'auto',
      pathinfo: isDev
    },
    infrastructureLogging: {
      level: DEBUG ? 'verbose' : isProd ? 'error' : 'verbose'
    },
    resolve: {
      modules: ['node_modules', paths.wsNodeModules],
      extensions: paths.moduleFileExtensions
        .map(str => `.${str}`)
        .filter(Boolean),
      alias: require('./webpack.resolveAlias'),
      fallback: {
        'react/jsx-runtime': 'react/jsx-runtime.js',
        'react/jsx-dev-runtime': 'react/jsx-dev-runtime.js',
        stream: require.resolve('stream-browserify'),
        buffer: require.resolve('buffer')
      }
    },
    resolveLoader: {
      modules: ['node_modules']
    },
    module: {
      strictExportPresence: true,
      rules: [
        {
          oneOf: [
            {
              test: [/\.bmp$/, /\.gif$/, /\.jpe?g$/, /\.png$/],
              loader: require.resolve('url-loader'),
              options: {
                limit: imageInlineSizeLimit,
                name: 'static/media/[name].[hash:8].[ext]'
              }
            },
            {
              test: /\.(js|mjs|jsx|ts|tsx)$/,
              exclude: /node_modules/,
              use: [require('./swcLoader')]
            },
            {
              test: /\.css$/,
              exclude: /\.module\.css$/,
              use: getStyleLoaders(isProd, sourceMap),
              sideEffects: true
            },
            {
              test: /\.svg$/,
              use: ['@svgr/webpack']
            },
            {
              loader: 'file-loader',
              exclude: /\.(js|mjs|jsx|ts|tsx|html|json)$/i,
              options: {
                name: 'static/media/[name].[hash:8].[ext]'
              }
            }
          ]
        }
      ]
    },
    plugins: [
      new GenerateJsonPlugin('site.webmanifest', {
        name: process.env.MFOX_PWA_APP_NAME,
        description:
          process.env.MFOX_PWA_APP_DESCRIPTION ||
          process.env.MFOX_SITE_DESCRIPTION,
        start_url: '/',
        short_name: process.env.MFOX_PWA_APP_NAME || process.env.MFOX_SITE_NAME,
        display: process.env.MFOX_PWA_DISPLAY || 'standalone',
        publicPath: '/',
        ...(process.env.MFOX_FAVICON_URL
          ? {
              icons: [
                {
                  src: process.env.MFOX_FAVICON_URL,
                  sizes: '192x192',
                  type: 'image/png'
                },
                {
                  src: process.env.MFOX_FAVICON_URL,
                  sizes: '192x192',
                  type: 'image/jpg'
                },
                {
                  src: process.env.MFOX_FAVICON_URL,
                  sizes: '64x64 32x32 24x24 16x16',
                  type: 'image/x-icon'
                }
              ]
            }
          : {})
      }),
      isProd && new WebpackManifestPlugin({}),
      new InterpolateHtmlPlugin(HtmlWebpackPlugin, includeEnv),
      // https://webpack.js.org/plugins/progress-plugin/#root
      !isBuildService &&
        new webpack.ProgressPlugin({
          activeModules: false,
          entries: false,
          dependencies: false,
          profile: false,
          percentBy: 'modules'
        }),
      // https://webpack.js.org/plugins/eslint-webpack-plugin/
      isDev && !isBuildService && new ESLintWebpackPlugin(eslintConfig),
      // https://www.npmjs.com/package/moment-locales-webpack-plugin
      new MomentLocalesPlugin({
        localesToKeep: process.env.MFOX_LOCALE_SUPPORTS
          ? process.env.MFOX_LOCALE_SUPPORTS.split(',')
          : ['en'],
        ignoreInvalidLocales: true
      }),
      new HtmlWebpackPlugin({
        hash: false,
        template: paths.appHtml,
        inject: 'body',
        scriptLoading: 'defer', // {'blocking'|'defer'|'module'}
        title: process.env.APP_NAME
      }),
      new MiniCssExtractPlugin(),
      isDev && !isBuildService && new CaseSensitivePathsPlugin(),
      // isDev && new webpack.EvalSourceMapDevToolPlugin({}),
      !isBuildService &&
        process.env.analyzer &&
        new BundleAnalyzerPlugin({ openAnalyzer: true }),
      // new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),

      //require only SSR
      // https://www.npmjs.com/package/@loadable/webpack-plugin
      // new LoadablePlugin({ filename: 'stats.json', writeToDisk: true }),
      new webpack.DefinePlugin(includeEnvDef),
      new webpack.ProvidePlugin({
        Buffer: ['buffer', 'Buffer']
      })
      // new webpack.ProvidePlugin({
      //   // do not remove me
      // })
    ].filter(Boolean),
    // Optimization
    optimization: {
      minimize: true,
      minimizer: [
        new TerserPlugin({
          parallel: true,
          minify: TerserPlugin.swcMinify,
          terserOptions: {
            format: {
              comments: false
            }
          },
          extractComments: false
        })
      ],
      emitOnErrors: true,
      chunkIds: 'named',
      // moduleIds: 'named',
      mergeDuplicateChunks: true,
      // nodeEnv: isProd ? 'production' : 'development',
      nodeEnv: false,
      mangleWasmImports: false,
      removeAvailableModules: true,
      removeEmptyChunks: true,
      mergeDuplicateChunks: false,
      flagIncludedChunks: true,
      // https://webpack.js.org/plugins/split-chunks-plugin/#splitchunksmaxasyncrequests
      splitChunks: {
        // chunks: 'async',
        minSize: 100 * KB,
        // maxSize: 500 * KB,
        // maxInitialSize: 500 * KB,
        // maxAsyncSize: 500 * KB,
        // minChunks: 1,
        hidePathInfo: true,
        // minRemainingSize: 0,
        // maxAsyncRequests: 16,
        maxInitialRequests: 8,
        // automaticNameDelimiter: '~',
        cacheGroups: {
          vendor: {
            test: /[\\/]node_modules[\\/](react|react-dom|@metafox\/framework\/app|react-router|react-intl|@formatjs|lodash|axios|fbjs)[\\/]/,
            name: 'vendor',
            chunks: 'initial', // async, all, initial
            // maxSize: 500 * KB, // package not yet minify,
            minSize: 30 * KB
          },
          mui: {
            test: /[\\/]node_modules[\\/](@mui\/material|@mui\/styles|@mui\/system)[\\/]/,
            name: 'material',
            chunks: 'initial', // async, all, initial
            // maxSize: 500 * KB, // package not yet minify,
            minSize: 30 * KB
          },
          commons: {
            test: /[\\/]node_modules[\\/](formik|react-redux|immer|react-router-dom|redux|@redux-saga|@reduxjs|pusher-js|moment|date-fns|immutable|@popperjs|laravel-echo|numeral)[\\/]/,
            name: 'commons',
            chunks: 'initial', // async, all, initial
            // maxSize: 500 * KB, // package not yet minify,
            minSize: 30 * KB
          },
          // framework: {
          //   test: /[\\/]packages\/framework[\\/]/,
          //   name: 'core',
          //   chunks: 'initial' // async, all, initial
          // },
          // main: {
          //   test: /[\\/]packages\/metafox[\\/](core)[\\/]/,
          //   name: 'main',
          //   chunks: 'initial' // async, all, initial
          // },
          app: {
            test: /[\\/]src[\\/]bundle-web[\\/]/,
            name: 'app',
            // initial allow dynamic import
            chunks: 'initial' // async, all, initial
          }
        }
      },
      runtimeChunk: {
        name: entry => `runtime-${entry.name}`
      }
    },
    watch: false,
    // watchOptions: {
    //   aggregateTimeout: 20000,
    //   poll: 1000
    // },
    // Dev Server config
    devServer: bundle
      ? undefined
      : {
          host,
          port,
          proxy,
          allowedHosts: disableFirewall ? 'all' : allowedHosts,
          headers: {
            'Access-Control-Allow-Origin': '*',
            'Access-Control-Allow-Methods': '*',
            'Access-Control-Allow-Headers': '*'
          },
          // Enable gzip compression of generated files.
          compress: false,
          static: {
            directory: paths.appPublic,
            publicPath: [paths.publicUrlOrPath],
            // By default files from `contentBase` will not trigger a page reload.
            watch: {
              ignored: ['/app/dist/']
            }
          },
          client: {
            webSocketURL: {
              hostname: process.env.WDS_SOCKET_HOST,
              pathname: process.env.WDS_SOCKET_PATH,
              port: process.env.WDS_SOCKET_PORT
            },
            overlay: {
              errors: true,
              warnings: false
            }
          },
          // devMiddleware: {
          //   publicPath: paths.publicUrlOrPath.slice(0, -1)
          // },
          https: false,
          historyApiFallback: {
            disableDotRule: true,
            index: paths.publicUrlOrPath
          }
        }
  };
};

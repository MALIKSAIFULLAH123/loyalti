const babelJest = require('babel-jest');
const { BabelHelper } = require('../workbox');
const includeEnv = require('../config/includeEnv');

module.exports = babelJest.default.createTransformer({
  babelrc: false,
  configFile: false,
  presets: [
    ['@babel/preset-env'],
    ['@babel/preset-react'],
    ['@babel/preset-typescript']
  ],
  plugins: [
    [require('@babel/plugin-transform-flow-strip-types').default, false],
    [require('babel-plugin-macros')],
    [require('@babel/plugin-proposal-optional-chaining').default],
    [require('@babel/plugin-proposal-nullish-coalescing-operator').default],
    [require('@babel/plugin-proposal-decorators').default, false],
    [
      require('@babel/plugin-proposal-class-properties').default,
      {
        loose: false
      }
    ],
    [require('@babel/plugin-proposal-numeric-separator').default],
    [
      require('@babel/plugin-transform-runtime').default,
      {
        corejs: false
      }
    ],
    [
      // inject more variable here.
      'transform-inline-environment-variables',
      {
        include: Object.keys(includeEnv)
      }
    ],
    [
      require('babel-plugin-module-resolver').default,
      {
        extensions: ['.ts', '.tsx', '.js', '.jsx', '.es', '.es6', '.mjs'],
        alias: BabelHelper.getModuleResolverAlias(),
        loglevel: 'silent'
      }
    ]
  ],
  overrides: []
});

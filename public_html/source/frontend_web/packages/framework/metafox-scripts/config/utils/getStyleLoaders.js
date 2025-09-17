const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const PostCssFlexBugFixes = require('postcss-flexbugs-fixes');
const PostCssNormalize = require('postcss-normalize');
const PostCssPresetEnv = require('postcss-preset-env')({
  autoprefixer: {
    flexbox: 'no-2009'
  },
  stage: 3
});

module.exports = function getStyleLoaders(isProd, sourceMap = false) {
  return [
    isProd
      ? {
          loader: MiniCssExtractPlugin.loader,
          options: {}
        }
      : {
          loader: 'style-loader',
          options: {}
        },
    {
      loader: 'css-loader',
      options: {
        importLoaders: 1,
        sourceMap
      }
    },
    {
      loader: 'postcss-loader',
      options: {
        sourceMap: sourceMap,
        postcssOptions: {
          plugins: [PostCssFlexBugFixes, PostCssPresetEnv, PostCssNormalize]
        }
      }
    }
  ].filter(Boolean);
};

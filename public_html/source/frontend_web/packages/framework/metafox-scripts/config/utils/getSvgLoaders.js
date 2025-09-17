module.exports = function getSvgLoaders() {
  return [
    { loader: 'babel-loader' },
    {
      loader: 'react-svg-loader',
      options: {}
    }
  ].filter(Boolean);
};

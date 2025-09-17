// link https://jestjs.io/docs/webpack#handling-static-assets
module.exports = {
  process() {
    return {
      code: 'module.exports = ""'
    };
  }
};

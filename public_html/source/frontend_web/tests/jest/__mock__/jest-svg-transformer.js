const path = require('path');

module.exports = {
  process(src, filePath) {
    return {
      code: 'module.exports="";'
    };
  }
};

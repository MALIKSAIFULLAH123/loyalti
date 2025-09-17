process.on('unhandledRejection', err => {
  throw err;
});

process.env.BABEL_ENV = 'test';
process.env.NODE_ENV = 'test';
process.env.PUBLIC_URL = '';

const jest = require('jest');

const jestConfig = require('../jest/jestConfig');

jest.run(['--config', JSON.stringify(jestConfig)]);

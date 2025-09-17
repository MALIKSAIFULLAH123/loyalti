process.env.NODE_ENV = 'development';
process.env.BABEL_ENV = 'development';

const reload = require('../helpers/reload');

try {
  reload();
} catch (err) {
  console.error(err);
  process.exit(1);
}

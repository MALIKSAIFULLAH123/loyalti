/* eslint-disable */

/**
 * dev0 server
 */

const http = require('http');

const { saveLayout, saveVariant } = require('./saveLayout');

function handlePost(req, res, callback) {
  let payload = '';

  if (typeof callback !== 'function') return null;

  const name = req.url.replace('/api/v1/dev0/', '').replace(/^\//, '');

  req.on('data', chunk => {
    payload += chunk;
  });

  req.on('end', () => {
    callback(name, JSON.parse(payload));
  });
}

http
  .createServer((req, res) => {
    // Get client req path name.
    if (req.method === 'POST') {
      handlePost(req, res, (name, payload) => {
        switch (name) {
          case 'ping':
            res.writeHead(200, 'OK', { 'Content-Type': 'application/json' });
            res.write(
              JSON.stringify({ status: 'success', data: { pong: true } })
            );
            res.end();
            break;
          case '/layout/snippet/publish':
            res.writeHead(403, 'OK', { 'Content-Type': 'application/json' });
            res.write(
              JSON.stringify({
                status: 'failure',
                data: 1,
                error:
                  'You are in develop mode, run `yarn bundle` to build layout.'
              })
            );
            res.end();
            break;
          case 'layout/snippet/theme':
            saveLayout(payload, req, res);
            break;
          case 'layout/snippet/variant':
            saveLayout(payload, req, res);
            break;
          default:
            res.write(
              JSON.stringify({
                success: false,
                error: 'Invalid request:' + name
              })
            );
            res.end();
        }
      });
    } else {
      res.writeHead(200, 'OK', { 'Content-Type': 'application/json' });
      res.write(
        JSON.stringify({ status: 'failure', data: 1, error: 'Invalid request' })
      );
      res.end();
    }
  })
  .listen(3002);

module.exports = function getClientEnvironment(environments, publicUrl) {
  const raw = Object.keys(environments).reduce(
    (env, key) => {
      env[key] = process.env[key];
      return env;
    },
    {
      NODE_ENV: process.env.NODE_ENV || 'development',
      PUBLIC_URL: publicUrl,
      WDS_SOCKET_HOST: process.env.WDS_SOCKET_HOST,
      WDS_SOCKET_PATH: process.env.WDS_SOCKET_PATH,
      WDS_SOCKET_PORT: process.env.WDS_SOCKET_PORT,
      FAST_REFRESH: 'false' !== process.env.FAST_REFRESH
    }
  );
  const stringified = {
    'process.env': Object.keys(raw).reduce((env, key) => {
      env[key] = JSON.stringify(raw[key]);
      return env;
    }, {})
  };

  return { raw, stringified };
};

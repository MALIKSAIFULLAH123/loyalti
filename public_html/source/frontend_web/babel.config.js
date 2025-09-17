module.exports = (api, opts, env) => {
  api.cache(false);

  return {
    presets: [
      process.env.BABEL_ENV !== 'es' && [
        '@babel/preset-env',
        {
          bugfixes: false,
          useBuiltIns: 'usage',
          corejs: 3,
          modules: ['esm', 'production-umd'].includes(process.env.BABEL_ENV)
            ? false
            : 'commonjs',
          targets: {
            browsers: 'Last 2 Chrome versions, Firefox ESR',
            node: 'current'
          }
        }
      ],
      [
        '@babel/preset-react',
        {
          development: process.env.BABEL_ENV !== 'build'
        }
      ],
      '@babel/preset-typescript'
    ].filter(Boolean),
    plugins: [].filter(Boolean),
    env: {
      build: {
        ignore: [
          '**/*.test.tsx',
          '**/*.test.ts',
          '**/*.story.tsx',
          '**/*.story.ts',
          '**/*.spec.ts',
          '**/*.spec.tsx',
          '__snapshots__',
          '__tests__',
          '__stories__'
        ]
      }
    },
    ignore: ['node_modules']
  };
};

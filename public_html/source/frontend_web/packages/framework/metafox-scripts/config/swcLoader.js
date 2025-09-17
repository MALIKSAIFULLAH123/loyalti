module.exports = {
  // $schema: 'https://json.schemastore.org/swcrc',
  loader: 'swc-loader',
  options: {
    jsc: {
      target: 'es2018',
      parser: {
        syntax: 'typescript',
        tsx: true,
        dynamicImport: true
      },
      // transform: null,
      // loose: false,
      // keepClassNames: false,
      experimental: {
        plugins: [
          ['@swc/plugin-loadable-components', {}],
          [
            '@swc/plugin-transform-imports',
            {
              lodash: {
                transform: 'lodash/{{member}}',
                preventFullImport: true
              },
              '@mui/icons-material': {
                transform: '@mui/icons-material/{{member}}',
                preventFullImport: true
              }
            }
          ]
        ]
      }
    },
    minify: true
  }
};

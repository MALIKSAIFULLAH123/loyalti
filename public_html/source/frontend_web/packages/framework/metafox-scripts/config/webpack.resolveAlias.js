const { BabelHelper } = require('../workbox');
const moduleResolverAlias = BabelHelper.getModuleResolverAlias();
module.exports = {
  ...moduleResolverAlias,
  'lodash-es': 'lodash',
  '@mui/material/esm/styled': '@mui/material/esm/styles/styled',
  '@mui/material/esm/useTheme': '@mui/material/esm/styles/useTheme'
};

import loadable from '@loadable/component';
import { BUNDLE_DIR } from '@metafox/framework';

const LayoutLoader = loadable.lib<{ theme: string }>(
  ({ theme }) => import(`@metafox/react-app/${BUNDLE_DIR}/theme.${theme}.tsx`),
  {
    cacheKey: ({ theme }) => `theme_${theme}`
  }
);

export default LayoutLoader;

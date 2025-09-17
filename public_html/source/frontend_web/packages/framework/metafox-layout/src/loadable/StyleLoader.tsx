import { BUNDLE_DIR } from '@metafox/framework/constants';
import loadable from '@loadable/component';

const StyleLoader = loadable.lib<{ theme: string; style: string }>(
  ({ theme, style }) =>
    import(
      `@metafox/react-app/${BUNDLE_DIR}/style.${style}.theme.${theme}.tsx`
    ),
  {
    cacheKey: ({ theme, style }) => `style.${style}.theme.${theme}`
  }
);

export default StyleLoader;

/**
 * @type: service
 * name: ThemeProvider
 */
import createCache from '@emotion/cache';
import { CacheProvider } from '@emotion/react';
import {
  IS_ADMINCP,
  MFOX_LOCALE,
  RTL_LOCALE,
  useGlobal
} from '@metafox/framework';
import { CssBaseline, useMediaQuery } from '@mui/material';
import createTheme from '@mui/material/styles/createTheme';
import MuiThemeProvider from '@mui/system/ThemeProvider';
import { merge } from 'lodash';
import React from 'react';
import rtlPlugin from 'stylis-plugin-rtl';
import useGlobalStyles from '@metafox/theme-default/GlobalCss.styles';
import * as mixins from './mixins';
import { detectBrowserLanguage } from '@metafox/utils';

type ThemeProviderProps = {
  children: React.ReactNode;
  infoLoader?: React.FC<{ themeId: string }>;
  themeType?: 'dark' | 'light' | 'auto';
  themeName?: string;
};

const CssGlobal = () => {
  useGlobalStyles();

  return null;
};

// Create rtl cache
const rltCache = createCache({
  key: 'rtl',
  stylisPlugins: [rtlPlugin]
});

const ltrCache = createCache({
  key: 'ltr',
  stylisPlugins: []
});

export default function ThemeProvider(props: ThemeProviderProps) {
  const { children, themeType: _forceThemeType, themeName } = props;
  const {
    layoutBackend,
    themeProcessor,
    usePreference,
    eventCenter,
    getSetting,
    use
  } = useGlobal();
  const [themeConfig, setThemeConfig] = React.useState(
    layoutBackend.getThemeConfig()
  );
  const {
    userLanguage,
    userDirection,
    themeType: _preferThemeType,
    themeId
  } = usePreference();

  const _themeType = _forceThemeType ?? _preferThemeType;

  const supports = getSetting<object>('localize.languages', { en: 1.0 });

  const langCode = (
    userLanguage ||
    detectBrowserLanguage(supports) ||
    MFOX_LOCALE
  ).toLowerCase();

  const rtl = !!(userDirection === 'rtl'
    ? true
    : RTL_LOCALE.find(x => langCode.startsWith(x)));

  const prefersDarkMode = useMediaQuery('(prefers-color-scheme: dark)');

  let themeType =
    _themeType === 'auto' && prefersDarkMode ? 'dark' : _themeType;

  if (IS_ADMINCP) {
    themeType = 'light';
  }

  if (document.body) {
    document.body.dir = rtl ? 'rtl' : 'ltr';
  }

  themeType = themeType === 'dark' ? 'dark' : 'light';

  // / handle theme on changed.
  React.useEffect(() => {
    if (eventCenter) {
      const id = eventCenter.on('onThemeChanged', setThemeConfig);

      return () => eventCenter.off('onThemeChanged', id);
    }
  }, [eventCenter]);

  const theme = React.useMemo(() => {
    const options = JSON.parse(JSON.stringify(themeConfig));

    const themeOptions = merge(
      {
        themeId,
        direction: rtl ? 'rtl' : 'ltr',
        palette: { mode: themeType }
      },
      options.default,
      themeType === 'dark' ? options.dark : {},
      themeName ? options[themeName] : {}
    );

    // process theme options

    const theme = createTheme(themeOptions);

    Object.keys(mixins).forEach(name => {
      theme.mixins[name] = mixins[name](theme);
    });

    themeProcessor.process(theme);

    // theme processor
    // collect all theme processor.

    return theme;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [themeConfig, themeId, themeName, themeType, rtl]);

  // root theme ui.
  use({ theme });

  if (_forceThemeType || themeName) {
    return (
      <CacheProvider value={rtl ? rltCache : ltrCache}>
        <MuiThemeProvider theme={theme}>{children}</MuiThemeProvider>
      </CacheProvider>
    );
  }

  return (
    <CacheProvider value={rtl ? rltCache : ltrCache}>
      <MuiThemeProvider theme={theme}>
        <CssBaseline />
        <CssGlobal />
        {children}
      </MuiThemeProvider>
    </CacheProvider>
  );
}

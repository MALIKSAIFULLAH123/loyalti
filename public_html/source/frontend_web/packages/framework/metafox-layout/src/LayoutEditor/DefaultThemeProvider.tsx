import { useGlobal } from '@metafox/framework';
import * as mixins from '@metafox/layout/mixins/index';
import themeConfig from '@metafox/theme-default/styles.json';
import createTheme from '@mui/material/styles/createTheme';
import ThemeProvider from '@mui/system/ThemeProvider';
import { merge } from 'lodash';
import React from 'react';

function DefaultThemeProvider({ children }) {
  const { usePreference, themeProcessor } = useGlobal();
  const { themeType = 'dark', userDirection = 'ltr' } = usePreference();

  const theme = React.useMemo(() => {
    const themeOptions = merge(
      {
        direction: userDirection,
        palette: { mode: themeType }
      },
      themeConfig.default,
      themeType === 'dark' ? themeConfig.dark : {}
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
  }, [themeType, userDirection]);

  return <ThemeProvider theme={theme}>{children}</ThemeProvider>;
}

export default DefaultThemeProvider;

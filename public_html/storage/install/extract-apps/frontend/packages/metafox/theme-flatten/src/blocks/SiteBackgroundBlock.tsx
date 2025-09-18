/**
 * @type: siteDock
 * name: theme-flatten.block.SiteBackgroundBlock
 * title: Site Background Block
 * bundle: web
 * theme: flatten
 */
import { useGlobal } from '@metafox/framework';
import { useTheme } from '@mui/material';
import React from 'react';

const SiteBackgroundBlock = () => {
  const theme = useTheme();
  const { getSetting, assetUrl } = useGlobal();
  const settingSiteBackground = getSetting('site-background');
  const bgDefault = assetUrl(
    `theme-flatten.${
      theme.palette.mode === 'dark'
        ? 'site_background_default_dark'
        : 'site_background_default_light'
    }`
  );
  const siteBackground = theme.siteBackground;

  React.useEffect(() => {
    if (settingSiteBackground?.collection) return;

    document.body.style.background = siteBackground;

    if (bgDefault) {
      document.body.style.backgroundImage = `url(${bgDefault})`;
    }
  }, [settingSiteBackground, siteBackground, bgDefault]);
};

export default SiteBackgroundBlock;

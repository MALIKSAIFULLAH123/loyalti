import { RouteLink, useGlobal } from '@metafox/framework';
import { styled, useTheme } from '@mui/material';
import React from 'react';

const Logo = styled('img', {
  name: 'LayoutSlot',
  slot: 'LogoLogin',
  overridesResolver(props, styles) {
   return [styles.logoLogin];
  }
})(({ theme }) => ({
  maxWidth: '100%',
  maxHeight: '48px'
}));

const LogoBranch = () => {
  const theme = useTheme();
  const { assetUrl, getSetting } = useGlobal();
  const homeUrl = getSetting('core.homepage_url');
  const logo =
    theme.palette.mode === 'dark'
      ? assetUrl('layout.image_logo_dark')
      : assetUrl('layout.image_logo');

  return (
    <RouteLink
      draggable={false}
      to={homeUrl || '/'}
      data-testid="linkLogoHomepage"
      role="link"
    >
      <Logo src={logo} alt="logo" />
    </RouteLink>
  );
};
export default LogoBranch;

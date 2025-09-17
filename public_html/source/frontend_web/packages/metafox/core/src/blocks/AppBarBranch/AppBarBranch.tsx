import { RouteLink, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { Box, useTheme } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

export const AppBarBranch = styled(Box, {
  name: 'AppBarLogo',
  slot: 'Root'
})(({ theme }) => ({
  paddingLeft: theme.spacing(2),
  height: theme.appBarHeight.normal ?? 58,
  display: 'flex',
  alignItems: 'center',
  '& a': {
    lineHeight: 0,
    display: 'inline-block'
  }
}));
type Props = {
  offline?: boolean;
};
export default React.forwardRef((prop: Props, ref) => {
  const theme = useTheme();
  const { offline } = prop || {};
  const { assetUrl, getSetting } = useGlobal();
  const homeUrl = getSetting('core.homepage_url');
  const logo =
    theme.palette.mode === 'dark'
      ? assetUrl('layout.image_logo_dark')
      : assetUrl('layout.image_logo');

  return (
    <Block>
      <BlockContent>
        <AppBarBranch>
          {offline ? (
            <img
              draggable={false}
              data-testid="imgLogo"
              src={logo}
              height="32"
              alt="Home"
            />
          ) : (
            <RouteLink
              draggable={false}
              to={homeUrl || '/'}
              ref={ref}
              data-testid="linkLogo"
              role="link"
            >
              <img
                draggable={false}
                data-testid="imgLogo"
                src={logo}
                height="32"
                alt="Home"
              />
            </RouteLink>
          )}
        </AppBarBranch>
      </BlockContent>
    </Block>
  );
});

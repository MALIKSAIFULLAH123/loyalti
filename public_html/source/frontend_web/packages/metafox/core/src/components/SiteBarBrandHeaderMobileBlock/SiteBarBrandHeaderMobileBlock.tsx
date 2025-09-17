/**
 * @type: block
 * name: core.siteBarBrandHeaderMobileBlock
 * title: SiteBar Brand Mobile
 * keywords: sidebar
 * mobile: true
 */
import {
  BlockViewProps,
  createBlock,
  Link,
  useGlobal
} from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { Button, styled, useTheme, Box, useScrollTrigger } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import AppBarSearch from '../../blocks/AppBarBlock/AppBarSearch';

const BlockHeader = styled('div', { name: 'BlockHeader' })<{
  minHeight?: number;
}>(({ theme, minHeight }) => ({
  position: 'relative',
  minHeight
}));

const MenuWrapper = styled(Box, { name: 'MenuWrapper' })<{
  active?: boolean;
  minHeight?: number;
}>(({ theme, active, minHeight }) => ({
  display: 'flex',
  height: minHeight,
  borderBottom: theme.mixins.border('secondary'),
  backgroundColor: theme.mixins.backgroundColor('paper'),
  zIndex: 1300,
  left: 0,
  right: 0,
  position: 'fixed',
  transitionDuration: '.5s',
  top: 0,
  alignItems: 'center',
  justifyContent: 'space-between',
  padding: theme.spacing(0, 2),
  ...(active
    ? {
        top: `-${minHeight}px`
      }
    : {})
}));

const MenuButtonIcon = styled(LineIcon, { name: 'MenuButtonIcon' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(18)
  })
);

const MenuButton = styled(Link, {
  name: 'MenuButton'
})(({ theme }) => ({
  marginRight: theme.spacing(-1),
  width: theme.spacing(5),
  height: theme.spacing(5),
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center'
}));

const Logo = styled(Link, { name: 'Logo' })(({ theme }) => ({
  height: 35,
  display: 'inline-block'
}));

const ButtonSignIn = styled(Button, { name: 'ButtonSignIn' })(({ theme }) => ({
  fontSize: 15,
  height: 32,
  padding: theme.spacing(0, 3),
  textTransform: 'capitalize'
}));

const SearchMobile = styled('div', { name: 'SearchMobile' })(({ theme }) => ({
  display: 'block',
  zIndex: '1301',
  position: 'absolute',
  backgroundColor: theme.palette.background.paper,
  top: 0,
  left: 0,
  width: '100%',
  height: '100%',
  '& > div': {
    width: '100%'
  },
  '& form': {
    width: 'calc(100% - 96px)'
  }
}));

const CancelButton = styled(Link, { name: 'CancelButton' })(({ theme }) => ({
  position: 'absolute',
  zIndex: theme.zIndex.appBar,
  right: '16px',
  top: '50%',
  transform: 'translateY(-50%)'
}));

function BaseBlock({ blockProps }: BlockViewProps) {
  const { i18n, useSession, navigate, assetUrl } = useGlobal();
  const { user: authUser } = useSession();
  const [openSearch, setOpenSearch] = React.useState<Boolean>(false);
  const theme = useTheme();
  const menuRef = React.useRef();
  const scrollTrigger = useScrollTrigger();
  const minHeight = theme.appBarMobileConfig?.nav ?? 48;

  const logo =
    theme.palette.mode === 'dark'
      ? assetUrl('layout.image_logo_dark')
      : assetUrl('layout.image_logo');

  const toggleOpen = () => {
    setOpenSearch(prev => !prev);
  };

  const signInButtonOnClick = () => {
    navigate({
      pathname: '/login'
    });
  };

  if (isEmpty(authUser)) {
    return (
      <Block>
        <BlockContent>
          <BlockHeader minHeight={minHeight}>
            <MenuWrapper
              ref={menuRef}
              active={scrollTrigger}
              minHeight={minHeight}
            >
              <Logo to="/" title={i18n.formatMessage({ id: 'home' })}>
                <img src={logo} height="30" alt="home" />
              </Logo>
              <ButtonSignIn
                variant="contained"
                color="primary"
                size="small"
                onClick={signInButtonOnClick}
                disableElevation
                type="submit"
              >
                {i18n.formatMessage({ id: 'sign_in' })}
              </ButtonSignIn>
            </MenuWrapper>
          </BlockHeader>
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block>
      <BlockContent>
        <BlockHeader minHeight={minHeight}>
          <MenuWrapper
            ref={menuRef}
            active={scrollTrigger}
            minHeight={minHeight}
          >
            <Logo to="/" title={i18n.formatMessage({ id: 'home' })}>
              <img src={logo} height="30" alt="home" />
            </Logo>
            <Box>
              <MenuButton role="button" underline="none" onClick={toggleOpen}>
                <MenuButtonIcon icon="ico-search-o" />
              </MenuButton>
              {openSearch ? (
                <SearchMobile>
                  <AppBarSearch
                    openSearch={openSearch}
                    menuRef={menuRef}
                    closeSearch={() => setOpenSearch(false)}
                  />
                  <CancelButton onClick={toggleOpen}>
                    {i18n.formatMessage({ id: 'cancel' })}
                  </CancelButton>
                </SearchMobile>
              ) : null}
            </Box>
          </MenuWrapper>
        </BlockHeader>
      </BlockContent>
    </Block>
  );
}

const SiteBarMobileBlock = createBlock<BlockViewProps>({
  name: 'SiteBarMobileBlock',
  extendBlock: BaseBlock,
  defaults: {}
});

export default SiteBarMobileBlock;

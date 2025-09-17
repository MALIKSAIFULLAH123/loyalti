import { RouteLink, useGlobal, useIsMobile } from '@metafox/framework';
import { UIBlockProps } from '@metafox/layout';
import { LineIcon, MenuItems } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import {
  AppBar,
  IconButton,
  Link,
  Toolbar,
  Tooltip,
  useTheme,
  Box
} from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import AdminSearchForm from './AdminSearchForm';
import subMenu from './adminSubMenu';
import useStyles from './styles';

const ToggleNavButton = styled(IconButton, {
  name: 'ToggleNavButton',
  slot: 'Root'
})({
  width: '48px',
  height: '48px',
  display: 'inline-flex',
  WebkitBoxAlign: 'center',
  msFlexAlign: 'center',
  alignItems: 'center',
  WebkitBoxPack: 'center',
  msFlexPack: 'center',
  justifyContent: 'center',
  color: '#555',
  fontSize: '16px',
  fontWeight: 'bold',
  cursor: 'pointer'
});

const AdminAppBarLogo = styled('div', {
  name: 'AdminAppBarLogo',
  slot: 'Root'
})(({ theme }) => ({
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  width: '220px',
  height: '60px',
  padding: '13px 8px',
  [theme.breakpoints.down('sm')]: {
    display: 'none'
  }
}));
const AdminAppBarLogoImg = styled('i', {
  name: 'AdminAppBarLogo',
  slot: 'Image'
})({
  backgroundSize: 'contain',
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center left',
  display: 'block',
  height: '32px'
});

const SubMenu = styled(Box, {
  name: 'AdminAppBar',
  slot: 'SubMenu'
})({
  display: 'inline-flex',
  justifyContent: 'flex-end',
  flex: 1,
  position: 'relative'
});

const FixSpace = styled('div', {
  name: 'AdminAppBar',
  slot: 'FixSpace'
})(({ theme }) => ({ height: theme.appBarHeight.normal ?? 58 }));

const MenuButton = styled(Link, { name: 'MenuButton' })<{
  activeMenu?: boolean;
}>(({ theme, activeMenu }) => ({
  flex: '1',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'end',
  padding: theme.spacing(0, 2)
}));

const MenuButtonIcon = styled(LineIcon, { name: 'MenuButtonIcon' })(
  ({ theme }) => ({
    width: 20,
    height: 22,
    fontSize: theme.mixins.pxToRem(24),
    color: theme.palette.grey[700]
  })
);

export type Props = UIBlockProps & {
  toggleDrawer: () => void;
  drawerVisible?: boolean;
};

export default function AdminAppBar({ toggleDrawer, drawerVisible }: Props) {
  const classes = useStyles();
  const isMobile = useIsMobile();
  const { assetUrl, i18n, setting, acl, useLoggedIn } = useGlobal();
  const theme = useTheme();
  const [openSearch, setOpenSearch] = React.useState<Boolean>(false);
  const menuRef = React.useRef();

  const toggleOpen = () => {
    setOpenSearch(prev => !prev);
  };

  const logo =
    theme.palette.mode === 'dark'
      ? assetUrl('layout.image_logo_dark')
      : assetUrl('layout.image_logo');

  const handleToggleDrawer = () => {
    if (toggleDrawer) toggleDrawer();
  };

  const handleAction = () => {};

  const pathname = '/admincp';

  const items = filterShowWhen(subMenu.items, {
    setting,
    acl
  });

  const loggedIn = useLoggedIn();

  if (!loggedIn) return null;

  return (
    <>
      <AppBar
        position="fixed"
        data-testid="layoutSlotHeader"
        sx={{ background: theme.palette?.appBar }}
      >
        <Toolbar sx={{ p: '0 !important' }}>
          <Tooltip
            title={i18n.formatMessage({
              id: drawerVisible ? 'collapse_menu' : 'expand_menu'
            })}
          >
            <ToggleNavButton
              onClick={handleToggleDrawer}
              data-testid="toggleMenu"
            >
              <LineIcon icon="ico-navbar" />
            </ToggleNavButton>
          </Tooltip>
          <AdminAppBarLogo>
            <RouteLink to="/" data-testid="imgLogo">
              <AdminAppBarLogoImg
                style={{
                  backgroundImage: `url(${logo})`
                }}
              />
            </RouteLink>
          </AdminAppBarLogo>
          {isMobile ? (
            <>
              <MenuButton onClick={toggleOpen}>
                <MenuButtonIcon icon="ico-search-o" />
              </MenuButton>
              {openSearch ? (
                <AdminSearchForm
                  menuRef={menuRef}
                  closeSearch={() => setOpenSearch(false)}
                />
              ) : null}
            </>
          ) : (
            <AdminSearchForm />
          )}
          <SubMenu data-testid="menuAppBar" pl={2}>
            <MenuItems
              prefixName="appbar.item."
              fallbackName="link"
              items={items}
              handleAction={handleAction}
              classes={classes}
              pathname={pathname}
            />
          </SubMenu>
        </Toolbar>
      </AppBar>
      <FixSpace />
    </>
  );
}

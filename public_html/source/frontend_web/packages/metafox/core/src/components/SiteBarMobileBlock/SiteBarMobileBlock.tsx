/**
 * @type: block
 * name: core.siteBarMobileBlock
 * title: SiteBar Mobile
 * keywords: sidebar
 * mobile: true
 */

import SideAppMenuBlock from '@metafox/core/blocks/SidebarAppMenu/Base';
import {
  BlockViewProps,
  createBlock,
  Link,
  useGlobal,
  useLocation,
  BROADCAST_CONNECTIONS_PUSHER_KEY,
  LINK_OFFLINE_MODE,
  useAppMenu,
  useActionControl
} from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { LineIcon, TruncateText, UserAvatar } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import {
  Popover,
  styled,
  Badge,
  Box,
  Chip,
  useScrollTrigger
} from '@mui/material';
import { isEmpty } from 'lodash';
import React, { Fragment } from 'react';
import AppBarSearch from '../../blocks/AppBarBlock/AppBarSearch';
import { getStatusSelector } from '@metafox/core/selectors/status';
import { useSelector } from 'react-redux';

const TabMenuData = [
  {
    to: '/',
    icon: 'ico-home-alt',
    appName: 'feed',
    scrollTop: true,
    showWhen: ['truthy', 'visibleMenus.home']
  },
  {
    to: '/messages',
    icon: 'ico-comment-o',
    appName: 'chatplus',
    name: 'new_chat_message',
    showWhen: [
      'and',
      ['truthy', 'setting.chatplus.server'],
      ['truthy', 'visibleMenus.message']
    ],
    action: 'chatplus/menu/clickItem'
  },
  {
    to: '/messages',
    icon: 'ico-comment-o',
    appName: 'chat',
    name: 'new_chat_message',
    showWhen: [
      'and',
      ['truthy', 'setting.chat.is_active'],
      ['falsy', 'setting.chatplus.server'],
      ['truthy', 'pusherService'],
      ['truthy', 'visibleMenus.message']
    ]
  },
  {
    to: '/notification',
    icon: 'ico-bell-o',
    appName: 'notification',
    name: 'new_notification',
    showWhen: ['truthy', 'visibleMenus.notification'],
    action: 'notification/menu/clickItem'

  },
  {
    icon: 'ico-search-o',
    appName: '',
    style: 'search',
    showWhen: [
      'or',
      ['truthy', 'visibleMenus.search'],
      ['eqeqeq', 'position', 'top']
    ]
  }
];

const MenuWrapper = styled('div', { name: 'MenuWrapper' })<{
  minHeight?: number;
  active?: boolean;
}>(({ theme, minHeight, active, position }) => ({
  display: 'flex',
  height: minHeight,
  backgroundColor: theme.mixins.backgroundColor('paper'),
  zIndex: 1300,
  left: 0,
  right: 0,
  position: 'fixed',
  transition: 'all .5s ease',
  ...(position === 'top'
    ? {
        borderBottom: theme.mixins.border('secondary'),
        bottom: 'auto',
        top: 0,
        ...(active
          ? {
              top: `-${minHeight}px`
            }
          : {})
      }
    : {
        borderTop: theme.mixins.border('secondary'),
        bottom: 0,
        ...(active
          ? {
              bottom: `-${minHeight}px`
            }
          : {})
      })
}));

const MenuButtonIcon = styled(LineIcon, { name: 'MenuButtonIcon' })(
  ({ theme }) => ({
    width: 24,
    height: 24,
    fontSize: theme.mixins.pxToRem(24)
  })
);

const MenuButton = styled(Link, {
  name: 'MenuButton',
  shouldForwardProp: prop => prop !== 'activeMenu'
})<{
  activeMenu?: boolean;
}>(({ theme, activeMenu }) => ({
  flex: '1',
  minWidth: '64px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  borderTop: 'solid 2px #fff',
  '.appbar-position-top &': {
    borderTop: 0,
    borderBottom: 'solid 2px #fff'
  },
  ...(activeMenu && {
    color: `${theme.palette.primary.main} !important`,
    borderColor: `${theme.palette.primary.main} !important`
  })
}));

const PopoverStyled = styled(Popover, { name: 'PopoverStyled' })(
  ({ theme }) => ({
    '& .MuiPopover-paper': {
      maxWidth: '100%',
      width: '100%',
      borderRadius: 0,
      top: '0px !important',
      bottom: 0,
      boxShadow: 'none !important',
      borderTop: `solid 1px ${theme.palette.border?.secondary}`,
      maxHeight: 'calc(100% - 49px)'
    }
  })
);

const DropdownMenuWrapper = styled('div', { name: 'DropdownMenuWrapper' })(
  ({ theme }) => ({
    width: '100%',
    minHeight: 'calc(100vh - 60px)'
  })
);

const UserBlock = styled('div', { name: 'UserBlock' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(2),
  borderBottom: `solid 1px ${theme.palette.border?.secondary}`
}));

const Avatar = styled('div', { name: 'Avatar' })(({ theme }) => ({
  width: 48,
  marginRight: theme.spacing(1)
}));

const UserInner = styled('div', { name: 'UserInner' })(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  display: 'flex',
  flexDirection: 'column'
}));

const UserName = styled(Box, { name: 'UserName' })(({ theme }) => ({
  color: theme.palette.text.primary,
  fontWeight: 'bold'
}));

const LinkInfo = styled(Link, { name: 'LinkInfo' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  lineHeight: 1,
  color: theme.palette.text.secondary
}));

const UserAction = styled('div', { name: 'UserAction' })(({ theme }) => ({
  marginLeft: 'auto',
  '& > .ico': {
    width: 32,
    height: 32,
    fontSize: theme.mixins.pxToRem(24),
    color: theme.palette.text.secondary,
    cursor: 'pointer',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
  }
}));

const MenuApp = styled('div', { name: 'MenuApp' })(({ theme }) => ({
  paddingTop: theme.spacing(0.5),
  '& ul': {
    marginLeft: theme.spacing(-1),
    paddingBottom: theme.spacing(4)
  },
  '& ul > li': {
    marginLeft: 0,
    marginRight: 0,
    marginBottom: theme.spacing(1),
    paddingLeft: 0,
    '& > a': {
      display: 'flex',
      padding: theme.spacing(1),
      fontSize: '0.9375rem',
      alignItems: 'center',
      fontWeight: 'bold'
    }
  }
}));

const SearchMobile = styled('div', { name: 'SearchMobile' })(({ theme }) => ({
  display: 'block',
  zIndex: '1301',
  position: 'absolute',
  backgroundColor: theme.palette.background.paper,
  top: '0',
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

const OfflineModeStyled = styled(Chip, { name: 'OfflineMode' })(
  ({ theme }) => ({
    marginTop: theme.spacing(1),
    width: 'fit-content',
    backgroundColor: theme.palette.warning.main,
    color: '#fff',
    cursor: 'pointer'
  })
);

const HeightFixed = styled('div', { name: 'HeightFixed' })<{
  heightApp?: number;
}>(({ heightApp }) => ({
  height: heightApp
}));

function BaseBlock({ blockProps, visibleMenus, slotName }: BlockViewProps) {
  const {
    i18n,
    useSession,
    navigate,
    usePageParams,
    getSetting,
    jsxBackend,
    getAcl,
    useIsMobile,
    useTheme,
    dispatch
  } = useGlobal();
  const position = slotName === 'header' ? 'top' : 'bottom';
  const location = useLocation();
  const session = useSession();
  const { user: authUser, loggedIn } = session;
  const [open, setOpen] = React.useState<Boolean>(false);
  const [openAccountMenu, setOpenAccountMenu] = React.useState<Boolean>(false);
  const [openSearch, setOpenSearch] = React.useState<Boolean>(false);
  const anchorRef = React.useRef<HTMLDivElement>();
  const setting: any = getSetting();
  const acl = getAcl();
  const isMobile = useIsMobile();
  const menuRef = React.useRef();
  const theme = useTheme();
  const minHeight = theme.appBarMobileConfig?.nav ?? 48;
  const status = useSelector(getStatusSelector);
  const FooterMenu = jsxBackend.get('core.ui.footerList');
  const hasAdminAccess = getAcl('core.admincp.has_admin_access');
  const offline = getSetting('core.offline');
  const accountMenu = useAppMenu('core', 'accountMenu');
  const [handleAction] = useActionControl(null, {});
  const scrollTrigger = useScrollTrigger();

  const [heightApp, setHeightApp] = React.useState(0);

  React.useEffect(() => {
    if (!loggedIn) return;

    document.body.classList.add(`appbar-${position}`);
  }, [position, loggedIn]);

  React.useEffect(() => {
    setHeightApp(menuRef.current?.clientHeight);
  }, []);

  const accountMenuFilter = filterShowWhen(accountMenu.items, {
    setting,
    acl,
    session,
    isMobile
  });

  const TabMenu = filterShowWhen(TabMenuData, {
    setting,
    pusherService: !!BROADCAST_CONNECTIONS_PUSHER_KEY,
    visibleMenus,
    position
  });

  const { appName = 'feed' } = usePageParams();

  const handleClick = () => {
    setOpen(prev => !prev);
  };

  const handleClose = () => {
    setOpen(prev => !prev);
  };

  const toggleOpen = () => {
    setOpenSearch(prev => !prev);
  };

  React.useEffect(() => {
    setOpen(false);
  }, [location.pathname]);

  React.useEffect(() => {
    if (!open) {
      setOpenAccountMenu(false);
    }
  }, [open]);

  const id = open ? 'dropdownMenuMobile' : undefined;

  const handleShowProfileMenu = () => {
    setOpenAccountMenu(prev => !prev);
  };

  const handleMenuClick = menu => {
    if (menu.scrollTop) {
      window.scrollTo(0, 0);
    }

    if (menu?.action) {
      dispatch({
        type: menu.action
      });
    }
  };

  if (isEmpty(authUser)) {
    return null;
  }

  return (
    <Block>
      <BlockContent>
        <HeightFixed heightApp={heightApp} />
        <MenuWrapper
          ref={menuRef}
          active={scrollTrigger}
          minHeight={minHeight}
          position={position}
          className={`appbar-position-${position}`}
        >
          {TabMenu.map((item, index) =>
            (item.style !== 'search' ? (
              <MenuButton
                key={`k${index}`}
                role="button"
                to={item.to}
                activeMenu={appName === item.appName && !open}
                underline="none"
                onClick={() => handleMenuClick(item)}
              >
                {status[item?.name] ? (
                  <Badge
                    color="error"
                    badgeContent={status[item?.name]}
                    max={99}
                  >
                    <MenuButtonIcon icon={item.icon} />
                  </Badge>
                ) : (
                  <MenuButtonIcon icon={item.icon} />
                )}
              </MenuButton>
            ) : (
              <Fragment key={`k${index}`}>
                <MenuButton
                  role="button"
                  to={item.to}
                  activeMenu={appName === item.appName && !open}
                  underline="none"
                  onClick={toggleOpen}
                >
                  <MenuButtonIcon icon={item.icon} />
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
              </Fragment>
            ))
          )}
          <MenuButton
            role="button"
            ref={anchorRef}
            activeMenu={open}
            onClick={handleClick}
            underline="none"
          >
            <MenuButtonIcon icon="ico-navbar" />
          </MenuButton>
        </MenuWrapper>
        <PopoverStyled
          id={id}
          open={Boolean(open)}
          anchorEl={anchorRef.current}
          onClose={handleClose}
          anchorReference="anchorPosition"
          anchorPosition={{ top: 0, left: 0 }}
          style={{ maxWidth: '100%' }}
          marginThreshold={0}
          transitionDuration={0}
          anchorOrigin={{
            vertical: 'top',
            horizontal: 'right'
          }}
          transformOrigin={{
            vertical: 'top',
            horizontal: 'right'
          }}
        >
          <DropdownMenuWrapper>
            <UserBlock>
              <Avatar>
                <UserAvatar user={authUser} size={48} />
              </Avatar>
              <UserInner>
                <TruncateText variant={'body1'} lines={2}>
                  <UserName>{authUser.full_name}</UserName>
                </TruncateText>
                <LinkInfo
                  onClick={() => {
                    handleClose();
                    navigate(authUser.link);
                  }}
                >
                  {authUser?.role?.name ||
                    i18n.formatMessage({ id: 'view_profile' })}
                </LinkInfo>
                {hasAdminAccess && offline && (
                  <a href={LINK_OFFLINE_MODE} target="_blank" rel="noreferrer">
                    <OfflineModeStyled
                      size="small"
                      label={i18n.formatMessage({
                        id: 'offline_mode'
                      })}
                    />
                  </a>
                )}
              </UserInner>
              <UserAction>
                <LineIcon
                  icon={
                    openAccountMenu
                      ? 'ico ico-angle-left'
                      : 'ico ico-angle-right'
                  }
                  onClick={handleShowProfileMenu}
                />
              </UserAction>
            </UserBlock>
            <MenuApp>
              {!openAccountMenu ? (
                <SideAppMenuBlock
                  appName="core"
                  menuName="primaryMenu"
                  displayViewMore={false}
                />
              ) : (
                <>
                  {accountMenuFilter.filter(Boolean).map((item, index) =>
                    jsxBackend.render({
                      component: `menuItem.as.${item.as || 'normal'}`,
                      props: {
                        key: index.toString(),
                        item,
                        variant: item?.variant || 'contained',
                        handleAction
                      }
                    })
                  )}
                </>
              )}
            </MenuApp>
            {FooterMenu && !openAccountMenu && (
              <Box sx={{ px: 2, pb: 2, pt: 1 }}>
                <FooterMenu color="inherit" />
              </Box>
            )}
          </DropdownMenuWrapper>
        </PopoverStyled>
      </BlockContent>
    </Block>
  );
}

const SiteBarMobileBlock = createBlock<BlockViewProps>({
  name: 'SiteBarMobileBlock',
  extendBlock: BaseBlock,
  defaults: {
    visibleMenus: {
      home: true,
      message: true,
      notification: true,
      search: false
    }
  },
  custom: {
    home: {
      component: 'Checkbox',
      name: 'visibleMenus.home',
      label: 'Show Home Icon',
      fullWidth: true
    },
    message: {
      component: 'Checkbox',
      name: 'visibleMenus.message',
      label: 'Show Message Icon',
      fullWidth: true
    },
    notification: {
      component: 'Checkbox',
      name: 'visibleMenus.notification',
      label: 'Show Notification Icon',
      fullWidth: true
    }
  }
});

export default SiteBarMobileBlock;

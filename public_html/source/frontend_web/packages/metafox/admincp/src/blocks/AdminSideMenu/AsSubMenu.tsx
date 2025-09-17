/**
 * @type: ui
 * name: adminSideMenu.as.subMenu
 * bundle: admincp
 */
import { RouteLink, useGlobal, useLocation } from '@metafox/framework';
import { LineIcon, MenuItemViewProps, ClickOutsideListener } from '@metafox/ui';
import React, { useEffect } from 'react';
import { filterShowWhen } from '@metafox/utils';
import {
  Box,
  Paper,
  Popper,
  styled,
  Typography,
  useMediaQuery
} from '@mui/material';
import { ScrollContainer } from '@metafox/layout';
import { orderBy } from 'lodash';

const name = 'AdminCpAsSubMenu';
const ACTIVE_BG = '#354661';

const MenuItemStyled = styled(Box, {
  name,
  slot: 'MenuItemStyled',
  shouldForwardProp: prop =>
    prop !== 'menuItemMinimize' &&
    prop !== 'menuItemMinimizeOpen' &&
    prop !== 'menuItemActive'
})<{
  menuItemMinimize?: boolean;
  menuItemMinimizeOpen?: boolean;
  menuItemActive?: boolean;
}>(({ theme, menuItemMinimize, menuItemMinimizeOpen, menuItemActive }) => ({
  fontSize: 14,
  display: 'flex',
  alignItems: 'center',
  minHeight: 24,
  justifyContent: 'center',
  cursor: 'pointer',
  userSelect: 'none', // harmed user selection
  position: 'relative',
  '&:before': {
    content: '""',
    position: 'absolute',
    top: 0,
    bottom: 0,
    left: 0,
    width: '3px',
    background: 'transparent'
  },
  '&:hover': {
    backgroundColor: 'rgba(0, 0, 0, 0.08)',
    color: 'rgb(238, 238, 238)',
    '&:before': {
      content: '""',
      position: 'absolute',
      top: 0,
      bottom: 0,
      left: 0,
      width: '3px',
      background: theme.palette.common.white
    }
  },
  ...(menuItemMinimize && {
    '& span.ico': {
      margin: '0 !important',
      textAlign: 'center'
    },
    '& > a': {
      padding: theme.spacing(1.5)
    }
  }),
  ...(menuItemMinimizeOpen && {
    '&:before': {
      background: theme.palette.common.white
    },
    '& span.ico': {
      color: theme.palette.common.white
    }
  }),
  ...(menuItemActive && {
    backgroundColor: ACTIVE_BG,
    color: theme.palette.common.white,

    '& > a': {
      color: theme.palette.common.white
    },
    ...(menuItemMinimize && {
      backgroundColor: `${ACTIVE_BG} !important`,
      color: 'rgb(255, 255, 255)',
      '& span.ico': {
        color: 'rgb(255, 255, 255) !important',
        textAlign: 'center'
      }
    })
  })
}));

const MenuItemLink = styled(Box, { name, slot: 'MenuItemLink' })(
  ({ theme }) => ({
    padding: theme.spacing(1.5),
    flex: 1,
    display: 'flex',
    alignItems: 'center',
    lineHeight: theme.spacing(3),
    minHeight: 48,
    color: 'rgba(238,238,238,0.7)',
    cursor: 'pointer',
    '&:hover': {
      '& span.ico': {
        color: theme.palette.common.white
      },
      color: theme.palette.common.white
    }
  })
);

const MenuItemIcon = styled(LineIcon, { name, slot: 'MenuItemIcon' })(
  ({ theme }) => ({
    width: 24,
    fontSize: 18,
    color: 'rgb(238, 238, 238,0.6)',
    display: 'inline-block',
    textAlign: 'center'
  })
);

const SubMenu = styled(Paper, { name, slot: 'SubMenu' })(({ theme }) => ({
  background: 'rgb(35, 48, 68)',
  width: '208px',
  borderRadius: 0
}));

const SubMenuItemLink = styled(RouteLink, {
  name,
  slot: 'SubMenuItemLink',
  shouldForwardProp: prop => prop !== 'minimize'
})<{ minimize: boolean }>(({ theme, minimize }) => ({
  padding: theme.spacing(1.5, 2),
  color: 'rgba(238, 238, 238, 0.7)',
  fontSize: 13,
  display: 'block',
  '&:hover': {
    backgroundColor: 'rgba(0, 0, 0, 0.08)',
    color: theme.palette.common.white
  },
  ...(!minimize && {
    padding: theme.spacing(1.5, 2, 1.5, 6)
  })
}));

const MenuItemLinkPoper = styled(Box, { name, slot: 'MenuItemLinkPoper' })(
  ({ theme }) => ({
    padding: theme.spacing(1.5, 2),
    color: 'rgba(238, 238, 238, 0.7)',
    fontSize: 13,
    display: 'block',
    '&:hover': {
      backgroundColor: 'rgba(0, 0, 0, 0.08)',
      color: theme.palette.common.white
    }
  })
);

const SubMenuItem = styled(Box, {
  name,
  slot: 'SubMenuItem',
  shouldForwardProp: prop => prop !== 'subMenuItemActive'
})<{ subMenuItemActive?: boolean }>(({ theme, subMenuItemActive }) => ({
  ...(subMenuItemActive && {
    backgroundColor: ACTIVE_BG,
    color: theme.palette.common.white,
    fontWeight: 'bold',
    '& > a': {
      color: theme.palette.common.white
    }
  })
}));

const IconArrow = styled(LineIcon, { name, slot: 'MenuItemIcon' })(
  ({ theme }) => ({
    marginLeft: 'auto',
    color: 'rgb(238, 238, 238,0.6)',
    fontSize: 13
  })
);

export default function MenuItem(props: MenuItemViewProps) {
  const {
    item,
    pathname,
    active: showActive,
    minimize,
    activeHover,
    handlePopoverClose,
    handlePopoverOpen
  } = props;
  const anchorRef = React.useRef<HTMLDivElement>();
  const { useSession, getAcl, getSetting, useTheme } = useGlobal();

  const [open, setOpen] = React.useState<boolean>(showActive);
  const session = useSession();
  const acl = getAcl();
  const setting = getSetting();
  const paperRef = React.useRef();
  const theme = useTheme();
  const scrollRef = React.useRef<HTMLDivElement>();

  const handleToggle = () => setOpen(open => !open);

  const isDesktop = useMediaQuery(theme.breakpoints.up('md'));

  const location = useLocation() as any;

  React.useEffect(() => {
    if (isDesktop) return;

    handlePopoverClose();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [location.pathname, isDesktop]);

  useEffect(() => {
    setOpen(showActive);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [showActive]);

  const items = filterShowWhen(item?.items, { session, acl, setting });

  const openTypeMinimize = minimize && activeHover;
  const openTypeFull = open && !minimize;

  if (!items?.length) return null;

  const sortItems = item?.order_by ? orderBy(items, [item?.order_by]) : items;

  return (
    <>
      <MenuItemStyled
        menuItemMinimize={minimize}
        menuItemMinimizeOpen={openTypeMinimize}
        menuItemActive={showActive && minimize}
        onClick={minimize ? handlePopoverOpen : handleToggle}
        data-testid={item.testid || item.name}
        onMouseEnter={handlePopoverOpen}
        onMouseLeave={handlePopoverClose}
        ref={anchorRef}
      >
        <MenuItemLink>
          <MenuItemIcon icon={item.icon} />
          {!minimize ? (
            <span style={{ marginLeft: 8 }}>{item.label}</span>
          ) : null}
          {!minimize ? (
            <IconArrow icon={open ? 'ico-angle-up' : 'ico-angle-down'} />
          ) : null}
        </MenuItemLink>
      </MenuItemStyled>
      {openTypeFull ? (
        <Box>
          {sortItems.map((subItem, index) => (
            <SubMenuItem
              subMenuItemActive={showActive && subItem.to === pathname}
              key={index.toString()}
              onClick={null}
            >
              <SubMenuItemLink
                minimize={minimize}
                to={subItem.to}
                data-testid={subItem.testid || subItem.name}
                target={subItem.target}
              >
                <span>{subItem.label}</span>
              </SubMenuItemLink>
            </SubMenuItem>
          ))}
        </Box>
      ) : null}
      {openTypeMinimize ? (
        <ClickOutsideListener
          onClickAway={handlePopoverClose}
          excludeRef={anchorRef}
        >
          <Popper
            id={`k${item.name}`}
            open={openTypeMinimize}
            placement={'right-start'}
            anchorEl={anchorRef.current}
            style={{
              zIndex: theme.zIndex.modal
            }}
            onMouseEnter={handlePopoverOpen}
          >
            <SubMenu ref={paperRef}>
              <ScrollContainer
                autoHide
                autoHeight
                autoHeightMax={'70vh'}
                ref={scrollRef}
              >
                <div onMouseLeave={handlePopoverClose}>
                  <MenuItemLinkPoper>
                    <Typography
                      fontWeight={600}
                      sx={{ color: '#fff !important' }}
                    >
                      {item.label}
                    </Typography>
                  </MenuItemLinkPoper>
                  <div>
                    {sortItems.map((subItem, index) => (
                      <SubMenuItem
                        subMenuItemActive={
                          showActive && subItem.to === pathname
                        }
                        key={index.toString()}
                        onClick={null}
                      >
                        <SubMenuItemLink
                          minimize={minimize}
                          to={subItem.to}
                          data-testid={subItem.testid || subItem.name}
                          target={subItem.target}
                        >
                          <span>{subItem.label}</span>
                        </SubMenuItemLink>
                      </SubMenuItem>
                    ))}
                  </div>
                </div>
              </ScrollContainer>
            </SubMenu>
          </Popper>
        </ClickOutsideListener>
      ) : null}
    </>
  );
}

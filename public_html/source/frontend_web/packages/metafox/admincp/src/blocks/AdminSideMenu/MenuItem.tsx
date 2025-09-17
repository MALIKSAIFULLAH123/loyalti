/**
 * @type: ui
 * name: adminSideMenu.as.normal
 * bundle: admincp
 */
import { RouteLink } from '@metafox/framework';
import { ClickOutsideListener, LineIcon, MenuItemViewProps } from '@metafox/ui';
import React from 'react';
import { Box, Paper, Popper, styled, Typography } from '@mui/material';

const name = 'AdminCpSideMenunNormal';
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
      margin: '0 !important'
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

const MenuItemLink = styled(RouteLink, { name, slot: 'MenuItemLink' })(
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

const SubMenuItemLink = styled(Box, { name, slot: 'SubMenuItemLink' })(
  ({ theme }) => ({
    padding: theme.spacing(1.5, 2),
    color: 'rgba(238, 238, 238, 0.7)',
    fontSize: 13,
    display: 'block'
  })
);

export default function MenuItem({
  item,
  handleAction,
  pathname,
  active: showActive,
  minimize,
  activeHover,
  handlePopoverClose,
  handlePopoverOpen
}: MenuItemViewProps) {
  const openTypeMinimize = minimize && activeHover;
  const anchorRef = React.useRef<HTMLDivElement>();
  const paperRef = React.useRef();

  return (
    <>
      <MenuItemStyled
        menuItemMinimize={minimize}
        menuItemMinimizeOpen={openTypeMinimize}
        menuItemActive={showActive && minimize}
        onMouseEnter={handlePopoverOpen}
        onMouseLeave={handlePopoverClose}
        ref={anchorRef}
      >
        <MenuItemLink
          to={item.to}
          data-testid={item.testid || item.name}
          onClick={item.value ? () => handleAction(item.value) : undefined}
        >
          <MenuItemIcon icon={item.icon} />
          {!minimize ? (
            <span style={{ marginLeft: 8 }}>{item.label}</span>
          ) : null}
        </MenuItemLink>
      </MenuItemStyled>
      <ClickOutsideListener
        onClickAway={handlePopoverClose}
        excludeRef={anchorRef}
      >
        <Popper
          id={`k${item.name}`}
          open={openTypeMinimize}
          placement={'right-start'}
          anchorEl={anchorRef.current}
          onMouseEnter={handlePopoverOpen}
        >
          <SubMenu ref={paperRef}>
            <div onMouseLeave={handlePopoverClose}>
              <SubMenuItemLink
              >
                <Typography fontWeight={600} sx={{ color: '#fff !important' }}>
                  {item.label}
                </Typography>
              </SubMenuItemLink>
            </div>
          </SubMenu>
        </Popper>
      </ClickOutsideListener>
    </>
  );
}

import { ButtonLink, RouteLink, useGlobal } from '@metafox/framework';
import { LineIcon, MenuItemProps } from '@metafox/ui';
import { Avatar, Box, Divider, Tooltip, styled } from '@mui/material';
import clsx from 'clsx';
import React from 'react';

const MenuItem = styled('div', {
  name: 'SidebarAppMenu',
  slot: 'menuItem',
  shouldForwardProp: prop => prop !== 'menuItemType',
  overridesResolver(props, styles) {
    return [styles.menuItem];
  }
})<{ menuItemType?: string }>(({ theme, menuItemType }) => ({
  ...(menuItemType === 'small' && {
    maxWidth: theme.spacing(7),
    margin: '0 auto'
  })
}));

const MenuItemIcon = styled(LineIcon, {
  name: 'SidebarAppMenu',
  slot: 'menuItemIcon',
  shouldForwardProp: prop =>
    prop !== 'activeMenuItem' && prop !== 'menuItemType',
  overridesResolver(props, styles) {
    return [
      styles.menuItemIcon,
      props.activeMenuItem && styles.activeMenuItem,
      props.menuItemType === 'small' && styles.menuItemIconSmall
    ];
  }
})(({ theme }) => ({
  marginRight: theme.spacing(2)
}));

const MenuItemText = styled('div', {
  name: 'SidebarAppMenu',
  slot: 'menuItemText',
  shouldForwardProp: prop =>
    prop !== 'activeMenuItem' && prop !== 'menuItemType',
  overridesResolver(props, styles) {
    return [
      styles.menuItemText,
      props.activeMenuItem && styles.activeMenuItem,
      props.menuItemType === 'small' && styles.menuItemSmall
    ];
  }
})(({ theme }) => ({
  fontWeight: theme.typography.fontWeightMedium
}));

function AsDivider({ dividerProps }: MenuItemProps) {
  return <Divider {...dividerProps} />;
}

function AsHeading({ label, classes }: MenuItemProps) {
  return <div className={classes.menuHeading}>{label}</div>;
}

function AsButton({
  classes,
  buttonProps,
  icon,
  to,
  name,
  label,
  testid
}: MenuItemProps) {
  return (
    <div className={classes.menuItemButton}>
      <ButtonLink
        {...buttonProps}
        to={to}
        role="button"
        data-testid={testid || name || label || icon}
      >
        {icon ? (
          <Box component="span" mr={0.5}>
            <LineIcon icon={icon} />
          </Box>
        ) : null}
        {label}
      </ButtonLink>
    </div>
  );
}

function AsLink({
  label,
  to,
  name,
  icon,
  image,
  alt,
  note,
  item_name,
  onClick,
  classes,
  active,
  testid,
  menuItemType
}: MenuItemProps) {
  return (
    <MenuItem
      className={clsx(classes.menuItem, active && classes.activeMenuItem)}
      role="menuitem"
      data-testid={testid || name || label || icon}
      menuItemType={menuItemType}
    >
      <RouteLink to={to} className={classes.menuItemLink} onClick={onClick}>
        {image || alt ? (
          <Avatar src={image} alt={alt} className={classes.menuItemAvatar} />
        ) : icon ? (
          <>
            {menuItemType === 'small' ? (
              <Tooltip title={label || testid || icon}>
                <MenuItemIcon
                  icon={icon}
                  className={classes.menuItemIcon}
                  activeMenuItem={active}
                  menuItemType={menuItemType}
                />
              </Tooltip>
            ) : (
              <MenuItemIcon
                icon={icon}
                className={classes.menuItemIcon}
                activeMenuItem={active}
                menuItemType={menuItemType}
              />
            )}
          </>
        ) : null}
        <MenuItemText activeMenuItem={active} menuItemType={menuItemType}>
          <span>{label}</span>
          {note ? (
            <span className={classes.shortcutModuleName}>{note}</span>
          ) : null}
          {item_name ? (
            <div className={classes.itemName}>{item_name}</div>
          ) : null}
        </MenuItemText>
      </RouteLink>
    </MenuItem>
  );
}

export default function SidebarMenuItem(props: MenuItemProps) {
  const { jsxBackend } = useGlobal();

  if ('divider' === props.as) {
    return <AsDivider {...props} />;
  }

  if ('heading' === props.as) {
    return <AsHeading {...props} />;
  }

  if ('button' === props.as) {
    return <AsButton {...props} />;
  }

  if (props.as) {
    if (jsxBackend.has(`sidebarMenuItem.ui.${props.as}`)) {
      return jsxBackend.render({
        component: `sidebarMenuItem.ui.${props.as}`,
        props
      });
    }
  }

  return <AsLink {...props} />;
}

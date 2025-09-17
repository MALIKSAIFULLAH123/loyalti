/**
 * @type: ui
 * name: menuItem.as.sidebarButton
 * chunkName: menuItemAs
 */
import { ButtonLink, useGlobal } from '@metafox/framework';
import { ControlMenuItemProps, LineIcon } from '@metafox/ui';
import { Button } from '@mui/material';
import React from 'react';

export default function SideBarButtonMenuItem(props: ControlMenuItemProps) {
  const { item, classes } = props;
  const { dispatch } = useGlobal();
  const { buttonProps, name, icon, to, value, label, asModal, testid } = item;

  if (value) {
    return (
      <div className={classes.menuItemButton}>
        <Button
          {...buttonProps}
          onClick={() => dispatch({ type: value })}
          role="button"
          data-testid={testid || label || icon}
          startIcon={icon ? <LineIcon icon={icon} /> : null}
          className={classes.buttonLink}
        >
          {label}
        </Button>
      </div>
    );
  }

  return (
    <div className={classes.menuItemButton}>
      <ButtonLink
        {...buttonProps}
        to={to}
        role="button"
        data-testid={testid || name || label || icon}
        className={classes.buttonLink}
        startIcon={icon ? <LineIcon icon={icon} /> : null}
        asModal={asModal}
      >
        {label}
      </ButtonLink>
    </div>
  );
}

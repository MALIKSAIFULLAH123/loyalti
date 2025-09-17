/**
 * @type: ui
 * name: menuItem.as.normal
 * chunkName: menuItemAs
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { ListItem, ListItemIcon, ListItemText } from '@mui/material';
import clsx from 'clsx';
import React from 'react';
import { useLocation } from 'react-router';
import useStyles from './styles';

export default function NormalMenuItem(props: any) {
  const { item, iconClassName, variant } = props;
  const { dispatch, i18n } = useGlobal();
  const location = useLocation();
  const classes = useStyles();

  const handleClick = evt => {
    dispatch({ type: 'menu/clicked', payload: props, meta: { evt, location } });
  };

  return (
    <ListItem
      className={clsx(item.className, classes.itemPrimary)}
      onClick={handleClick}
      data-testid={item.testid || item.name || item.label || item.icon}
      variant={variant}
    >
      <ListItemIcon>
        <LineIcon
          variant="listItemIcon"
          className={clsx(iconClassName, item.color && classes[item.color])}
          icon={item.icon}
        />
      </ListItemIcon>
      {item.label ? (
        <ListItemText
          primary={i18n.formatMessage({ id: item.label })}
          className={clsx(item?.active && classes.classActiveButton)}
        />
      ) : null}
    </ListItem>
  );
}

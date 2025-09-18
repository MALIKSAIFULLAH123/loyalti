/**
 * @type: ui
 * name: menuItem.as.storyItemLabel
 * chunkName: menuItemAs
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { ListItem, ListItemIcon, ListItemText, Theme } from '@mui/material';
import clsx from 'clsx';
import React from 'react';
import { useLocation } from 'react-router';
import { createStyles, makeStyles } from '@mui/styles';
import { isEmpty } from 'lodash';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      iconStyle: {
        paddingRight: theme.spacing(1)
      },
      itemPrimary: {
        '&:hover': {
          backgroundColor: theme.palette.background.default,
          cursor: 'pointer'
        }
      },
      success: {
        '&.ico': {
          color: theme.palette.success.main
        }
      },
      gray: {
        '&.ico': {
          color: theme.palette.grey['500']
        }
      },
      warning: {
        '&.ico': {
          color: theme.palette.warning.main
        }
      },
      danger: {
        '&.ico': {
          color: theme.palette.error.main
        }
      },
      classActiveButton: {
        color: theme.palette.primary.main,
        '& span': {
          fontWeight: theme.typography.fontWeightBold
        }
      }
    }),
  { name: 'MenuItem' }
);

export default function NormalMenuItem(props: any) {
  const { item, iconClassName, variant, identity } = props;
  const { dispatch, i18n, useGetItem } = useGlobal();
  const location = useLocation();
  const classes = useStyles();
  const story = useGetItem(identity);
  const user = useGetItem(story?.user);

  const handleClick = evt => {
    dispatch({ type: 'menu/clicked', payload: props, meta: { evt, location } });
  };

  if (isEmpty(user)) return null;

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
      <ListItemText
        primary={i18n.formatMessage({ id: item.label }, { user_name: user?.full_name || user?.title })}
        className={clsx(item?.active && classes.classActiveButton)}
      />
    </ListItem>
  );
}

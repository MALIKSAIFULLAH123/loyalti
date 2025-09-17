/**
 * @type: ui
 * name: appbar.item.link
 * chunkName: appbarAs
 */
import { RouteLink, useGlobal } from '@metafox/framework';
import { LineIcon, MenuItemViewProps as Props } from '@metafox/ui';
import { Badge, Tooltip } from '@mui/material';
import clsx from 'clsx';
import React from 'react';
import { useDispatch } from 'react-redux';

export default function AsLinkItem({ item, classes }: Props) {
  const dispatch = useDispatch();
  const { i18n } = useGlobal();

  const handleClick = item.value
    ? () => dispatch({ type: item.value, payload: item.params })
    : undefined;

  return (
    <RouteLink
      className={clsx(classes.menuButton, classes.menuRefIndex)}
      to={item.to}
      onClick={handleClick}
      data-testid={item.testid || item.name}
    >
      <span
        role="link"
        className={clsx(
          classes.smallMenuButton,
          item.active && classes.menuButtonActive
        )}
      >
        <Tooltip
          title={item?.label ? i18n.formatMessage({ id: item?.label }) : ''}
        >
          <Badge color="error">
            <LineIcon className={classes.smallMenuIcon} icon={item.icon} />
          </Badge>
        </Tooltip>
      </span>
    </RouteLink>
  );
}

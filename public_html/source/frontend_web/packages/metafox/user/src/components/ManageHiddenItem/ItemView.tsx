import { useGlobal } from '@metafox/framework';
import { ManageHiddenItemProps } from '@metafox/feed/types';
import {
  ItemAction,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  UserAvatar,
  UserName
} from '@metafox/ui';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import { Button } from '@mui/material';

export default function FeedHiddenItemView({
  item,
  user,
  actions,
  identity,
  itemProps,
  wrapAs,
  wrapProps
}: ManageHiddenItemProps) {
  const { i18n, dispatch } = useGlobal();

  const handleUnhideButton = () => {
    dispatch({
      type: 'unSnooze',
      payload: { identity }
    });
  };

  const handleUndoButton = () => {
    dispatch({
      type: 'snoozeForever',
      payload: { identity }
    });
  };

  if (!item) return null;

  const { extra, link: to } = item;
  const { can_snooze_forever, can_unsnooze } = extra || {};

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={user} size={48} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <UserName to={to} user={user} color={'inherit'} hoverCard={false} />
        </ItemTitle>
      </ItemText>
      <ItemAction>
        {can_unsnooze ? (
          <Button
            size="medium"
            variant="outlined"
            color="primary"
            onClick={handleUnhideButton}
          >
            {i18n.formatMessage({ id: 'unhide' })}
          </Button>
        ) : null}
        {can_snooze_forever ? (
          <Button
            size="medium"
            variant="outlined"
            color="primary"
            onClick={handleUndoButton}
          >
            {i18n.formatMessage({ id: 'undo' })}
          </Button>
        ) : null}
      </ItemAction>
    </ItemView>
  );
}

FeedHiddenItemView.LoadingSkeleton = LoadingSkeleton;

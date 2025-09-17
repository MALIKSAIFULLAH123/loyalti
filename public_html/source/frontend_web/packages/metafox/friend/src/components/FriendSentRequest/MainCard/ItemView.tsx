import { Link, useGlobal } from '@metafox/framework';
import { FRIENDSHIP_REQUEST_SENT } from '@metafox/friend/constant';
import {
  ButtonList,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  UserAvatar
} from '@metafox/ui';
import { Button } from '@mui/material';
import React from 'react';
import { FriendRequestItemProps } from '../../../types';

export default function FriendRequest({
  item,
  user,
  identity,
  actions,
  wrapAs,
  wrapProps
}: FriendRequestItemProps) {
  const { i18n } = useGlobal();

  if (!item || !user || FRIENDSHIP_REQUEST_SENT !== item.friendship)
    return null;

  const { friendship } = item;
  const { link: to } = user;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <Link to={to} color={'inherit'} role="link">
          <UserAvatar user={user} size={80} />
        </Link>
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Link
            to={to}
            color={'inherit'}
            role="link"
            hoverCard={`/user/${user?.id}`}
          >
            {user.full_name}
          </Link>
        </ItemTitle>
        {FRIENDSHIP_REQUEST_SENT === friendship ? (
          <ButtonList variant="fillFirst" spacing="medium">
            <Button
              variant="contained"
              color="primary"
              size="small"
              onClick={actions.cancelFriendRequest}
              startIcon={<LineIcon icon="ico-user2-del-o" />}
              sx={{ fontWeight: 700, flex: 'unset !important' }}
            >
              {i18n.formatMessage({ id: 'cancel_request' })}
            </Button>
          </ButtonList>
        ) : null}
      </ItemText>
    </ItemView>
  );
}

FriendRequest.displayName = 'FriendRequestMainCard';

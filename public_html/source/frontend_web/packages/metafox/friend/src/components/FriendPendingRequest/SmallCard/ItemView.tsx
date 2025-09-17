import { useGlobal } from '@metafox/framework';
import { FRIENDSHIP_CONFIRM_AWAIT } from '@metafox/friend';
import {
  ButtonList,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  Statistic,
  UserAvatar
} from '@metafox/ui';
import { Box, Button, ClickAwayListener } from '@mui/material';
import * as React from 'react';
import { FriendRequestItemProps } from '../../../types';

export default function FriendRequest({
  item,
  identity: idRequest,
  user,
  wrapAs,
  wrapProps,
  actions
}: FriendRequestItemProps) {
  const { i18n, dispatch, navigate, useSession } = useGlobal();
  const [active, setActive] = React.useState<boolean>(false);
  const { user: authUser } = useSession();

  if (!item || !user || FRIENDSHIP_CONFIRM_AWAIT !== item.friendship)
    return null;

  const { friendship } = item;
  const { statistic, link: to, profile_settings } = user;
  
  const isAuthUser = authUser?.id === item.id;

  const handleClickAway = () => {
    setActive(false);
  };

  const denyFriendRequest = evt => {
    if (evt) {
      evt.preventDefault();
      evt.stopPropagation();
    }

    const identity = `user.entities.user.${user.id}`;

    dispatch({
      type: 'user/denyFriendRequest',
      payload: { identity, idRequest }
    });
  };

  const acceptFriendRequest = evt => {
    if (evt) {
      evt.preventDefault();
      evt.stopPropagation();
    }

    const identity = `user.entities.user.${user.id}`;

    dispatch({
      type: 'user/acceptFriendRequest',
      payload: { identity, idRequest }
    });
  };

  const navigateTo = (to: string) => {
    setActive(true);
    navigate({ pathname: to }, { state: { asChildPage: true } });
  };

  const showMutualFriends = evt => {
    if (evt) {
      evt.preventDefault();
      evt.stopPropagation();
    }

    actions.showMutualFriends();
  };

  return (
    <ClickAwayListener onClickAway={handleClickAway}>
      <ItemView
        testid={`${item.resource_name}`}
        data-eid={idRequest}
        wrapAs={wrapAs}
        wrapProps={wrapProps}
        selected={active}
        onClick={() => navigateTo(to)}
        role="button"
      >
        <ItemMedia>
          <UserAvatar user={user} size={48} noLink />
        </ItemMedia>
        <ItemText>
          <ItemTitle>{user.full_name}</ItemTitle>
          {!isAuthUser && statistic.total_mutual > 0 && profile_settings?.profile_view_profile ? (
            <Box>
              <ItemSummary role="button" onClick={showMutualFriends}>
                <Statistic values={statistic} display="total_mutual" />
              </ItemSummary>
            </Box>
          ) : null}
          {FRIENDSHIP_CONFIRM_AWAIT === friendship ? (
            <ButtonList variant="fillWidth" spacing="medium">
              <Button
                variant="contained"
                color="primary"
                size="small"
                onClick={acceptFriendRequest}
                startIcon={<LineIcon icon="ico-check" />}
              >
                {i18n.formatMessage({ id: 'confirm' })}
              </Button>
              <Button
                size="small"
                variant="outlined"
                color="primary"
                startIcon={<LineIcon icon="ico-close" />}
                onClick={denyFriendRequest}
              >
                {i18n.formatMessage({ id: 'delete' })}
              </Button>
            </ButtonList>
          ) : null}
        </ItemText>
      </ItemView>
    </ClickAwayListener>
  );
}

FriendRequest.displayName = 'FriendRequestSmallCard';

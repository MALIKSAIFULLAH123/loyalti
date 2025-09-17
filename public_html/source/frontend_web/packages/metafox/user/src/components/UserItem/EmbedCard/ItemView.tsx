import { Link, useGlobal } from '@metafox/framework';
import {
  FRIENDSHIP_CAN_ADD_FRIEND,
  FRIENDSHIP_IS_FRIEND,
  FRIENDSHIP_REQUEST_SENT
} from '@metafox/friend';
import {
  FeedEmbedCardProps,
  LineIcon,
  Statistic,
  TruncateText,
  UserAvatar
} from '@metafox/ui';
import { UserItemProps, UserItemShape } from '@metafox/user';
import { Box, Button } from '@mui/material';
import { styled } from '@mui/material/styles';
import clsx from 'clsx';
import React from 'react';
import useStyles from './styles';

const ItemInner = styled('div', { name: 'ItemInner' })(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'center',
  marginLeft: theme.spacing(2)
}));

const ButtonFriendshipStyled = styled(Box, {
  name: 'ItemInner',
  slot: 'ButtonFriendshipStyled',
  shouldForwardProp: prop => prop !== 'totalMutual'
})<{ totalMutual?: boolean }>(({ theme, totalMutual }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-end',
  marginTop: totalMutual ? theme.spacing(2) : theme.spacing(1)
}));

type EmbedUserItemProps = { item: UserItemShape } & FeedEmbedCardProps &
  UserItemProps;

const mappingFriendship = (friendship: number) => {
  const detail = {
    [FRIENDSHIP_CAN_ADD_FRIEND]: {
      icon: 'ico-user3-plus-o',
      textId: 'add_friend',
      disabled: false
    },
    [FRIENDSHIP_REQUEST_SENT]: {
      icon: 'ico-user3-clock-o',
      textId: 'request_sent',
      disabled: true
    },
    [FRIENDSHIP_IS_FRIEND]: {
      icon: 'ico-user3-check-o',
      textId: 'friend',
      disabled: true
    }
  };

  return detail[friendship];
};

export default function EmbedUserItem({
  item,
  variant,
  actions
}: EmbedUserItemProps) {
  const classes = useStyles();
  const { i18n, useSession } = useGlobal();

  const { user: authUser } = useSession();

  if (!item) return null;

  const isAuthUser = authUser?.id === item.id;

  const { full_name, statistic, link, friendship, extra, profile_settings } =
    item;

  const friendshipItem =
    friendship === FRIENDSHIP_CAN_ADD_FRIEND && !extra?.can_add_friend
      ? undefined
      : mappingFriendship(friendship);

  return (
    <div
      data-testid={'feedEmbedUserList'}
      className={clsx(classes.item, classes[variant])}
    >
      <div className={classes.itemOuter}>
        <div className={classes.media}>
          <UserAvatar user={item} size={120} />
        </div>
        <ItemInner>
          <TruncateText lines={2} variant="h4" sx={{ mb: 1 }}>
            <Link color="inherit" to={link}>
              {full_name}
            </Link>
          </TruncateText>
          {profile_settings?.profile_view_profile && !isAuthUser ? (
            <Box className={classes.statistic}>
              <div role="button" onClick={actions.presentMutualFriends}>
                <Statistic values={statistic} display="total_mutual" />
              </div>
            </Box>
          ) : null}
          {friendshipItem && (
            <ButtonFriendshipStyled
              totalMutual={Boolean(statistic?.total_mutual)}
            >
              <Box display="flex" alignItems="center">
                <Button
                  color="primary"
                  variant="contained"
                  disabled={friendshipItem.disabled}
                  onClick={actions.addFriend}
                  size="small"
                  startIcon={<LineIcon icon={friendshipItem.icon} />}
                >
                  {i18n.formatMessage({ id: friendshipItem.textId })}
                </Button>
              </Box>
            </ButtonFriendshipStyled>
          )}
        </ItemInner>
      </div>
    </div>
  );
}

import { Link, useGlobal, useSession } from '@metafox/framework';
import { FriendRequestItemProps } from '@metafox/friend/types';
import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  Statistic,
  UserAvatar
} from '@metafox/ui';
import { Box, Button, Typography, styled } from '@mui/material';
import * as React from 'react';

const Root = styled(ItemView, { slot: 'root', name: 'FriendItem' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center'
  })
);
const ItemContent = styled('div', { slot: 'ItemContent', name: 'FriendItem' })(
  ({ theme }) => ({
    display: 'flex',
    flex: 1,
    minWidth: 0,
    justifyContent: 'space-between',
    alignItems: 'center',
    width: '100%'
  })
);

const ItemUser = styled(Box, {
  slot: 'ItemUser',
  name: 'ItemUser'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'baseline',
  '& > div': {
    flex: 'none',
    flexDirection: 'row'
  },
  '&>div:not(:last-child):after': {
    textDecoration: 'none',
    content: '"·"',
    paddingLeft: '0.4em',
    paddingRight: '0.4em',
    fontWeight: 'normal',
    color: theme.palette.text.secondary
  }
}));

export default function FriendItem({
  identity,
  item,
  wrapProps,
  wrapAs,
  actions
}: FriendRequestItemProps) {
  const { user: authUser } = useSession();
  const { i18n, dispatch, useGetItem } = useGlobal();
  const user = useGetItem(item?.user);

  if (!item) return null;

  const { link: to, extra, is_owner: isOwner } = item;
  const { statistic } = user;
  const isAuthUser = authUser?.id === item.id;

  return (
    <Root
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemContent>
        <ItemMedia>
          <UserAvatar user={item} size={48} />
        </ItemMedia>
        <ItemText>
          <ItemTitle>
            <Link to={to} children={item.full_name} color={'inherit'} />
          </ItemTitle>
          <ItemUser>
            {isOwner ? (
              <ItemSummary>
                <Typography color="text.secondary">
                  {i18n.formatMessage({ id: 'owner' })}
                </Typography>
              </ItemSummary>
            ) : null}
            {!isAuthUser && statistic?.total_mutual > 0 ? (
              <ItemSummary role="button">
                <Statistic
                  values={statistic}
                  display="total_mutual"
                  onClick={actions.showMutualFriends}
                />
              </ItemSummary>
            ) : null}
          </ItemUser>
        </ItemText>
      </ItemContent>
      {extra?.can_remove && (
        <Box ml={1}>
          <Button
            variant="contained"
            color="primary"
            onClick={() =>
              dispatch({
                type: 'saved/removeMemberInCollection',
                payload: { identity }
              })
            }
          >
            {i18n.formatMessage({ id: 'remove' })}
          </Button>
        </Box>
      )}
    </Root>
  );
}

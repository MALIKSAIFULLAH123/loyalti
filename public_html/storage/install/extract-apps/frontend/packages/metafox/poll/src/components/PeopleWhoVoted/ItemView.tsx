import { Link, useGlobal } from '@metafox/framework';
import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  Statistic,
  UserAvatar
} from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';

const name = 'PeopleWhoVotedPoll';
const LinkStyled = styled(Link, { name })(({ theme }) => ({
  ...theme.typography.h5
}));

export default function PeopleWhoVotedPoll({
  identity,
  item,
  user,
  wrapAs,
  wrapProps
}) {
  const { useSession } = useGlobal();
  const { user: authUser } = useSession();

  if (!item || !user) return null;

  const { statistic, link: to, full_name } = user;
  const isAuthUser = authUser?.id === user.id;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={user as any} size={48} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <LinkStyled to={to}>{full_name}</LinkStyled>
        </ItemTitle>
        {!isAuthUser ? (
          <ItemSummary>
            <Statistic values={statistic} display="total_mutual" />
          </ItemSummary>
        ) : null}
      </ItemText>
    </ItemView>
  );
}

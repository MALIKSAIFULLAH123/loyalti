import { EventItemProps } from '@metafox/event/types';
import { useGlobal, useSession } from '@metafox/framework';
import {
  ItemAction,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  Statistic,
  UserAvatar,
  UserName
} from '@metafox/ui';
import { Button, styled } from '@mui/material';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

const Root = styled(ItemView, { slot: 'root', name: 'FriendItem' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    '.MuiGrid-root.MuiGrid-item:last-child &': {
      borderBottom: 'none'
    }
  })
);

const ButtonInviteStyled = styled(Button, { name: 'ButtonInviteStyled' })(
  ({ theme }) => ({
    fontWeight: theme.typography.fontWeightBold
  })
);

export default function GuestSmallCard({
  identity,
  user,
  wrapProps,
  wrapAs,
  item,
  actions
}: EventItemProps) {
  const { i18n } = useGlobal();
  const { user: authUser } = useSession();
  const [isSubmit, setIsSubmit] = React.useState(false);

  if (!user) return null;

  const onAction = () => {
    setIsSubmit(true);
    actions.removeMemberGuest(() => setIsSubmit(false));
  };

  const { statistic, link: to } = user;
  const isAuthUser = authUser?.id === user.id;

  return (
    <Root
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${user.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={user} size={48} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <UserName to={to} user={user} color={'inherit'} hoverCard={false} />
        </ItemTitle>
        {!isAuthUser ? (
          <ItemSummary role="button" onClick={actions.showMutualFriends}>
            <Statistic values={statistic} display="total_mutual" />
          </ItemSummary>
        ) : null}
      </ItemText>
      {item.extra.can_delete ? (
        <ItemAction>
          <ButtonInviteStyled
            size="small"
            variant="outlined"
            onClick={onAction}
            disabled={isSubmit}
          >
            {i18n.formatMessage({ id: 'remove' })}
          </ButtonInviteStyled>
        </ItemAction>
      ) : null}
    </Root>
  );
}

GuestSmallCard.LoadingSkeleton = LoadingSkeleton;

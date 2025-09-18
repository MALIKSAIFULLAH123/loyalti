import { EventDetailViewProps } from '@metafox/event';
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
import { styled, Button } from '@mui/material';
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
const ItemContent = styled('div', { slot: 'ItemContent', name: 'FriendItem' })(
  () => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    width: '100%'
  })
);

const UserStyle = styled('div', { slot: 'UserStyle', name: 'FriendItem' })(
  () => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    flex: 1, 
    overflow: 'hidden'
  })
);

const ButtonInviteStyled = styled(Button, { name: 'ButtonInviteStyled' })(
  ({ theme }) => ({
    fontWeight: theme.typography.fontWeightBold
  })
);

export default function HostSmallerCard({
  identity,
  user,
  item,
  wrapProps,
  wrapAs,
  actions
}: EventDetailViewProps) {
  const { i18n } = useGlobal();
  const { user: authUser } = useSession();
  const [isSubmit, setIsSubmit] = React.useState(false);

  if (!user || !item) return null;

  const { statistic, link: to } = user;
  const { extra } = item;
  const isAuthUser = authUser?.id === user.id;

  const onAction = () => {
    setIsSubmit(true);
    actions.removeHost(() => setIsSubmit(false));
  };

  return (
    <Root
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${user.resource_name}`}
      data-eid={identity}
    >
      <ItemContent>
        <UserStyle>
          <ItemMedia>
            <UserAvatar user={user} size={48} />
          </ItemMedia>
          <ItemText>
            <ItemTitle>
              <UserName
                to={to}
                user={user}
                color={'inherit'}
                hoverCard={false}
              />
            </ItemTitle>
            {!isAuthUser ? (
              <ItemSummary role="button" onClick={actions.showMutualFriends}>
                <Statistic values={statistic} display="total_mutual" />
              </ItemSummary>
            ) : null}
          </ItemText>
        </UserStyle>
        {extra?.can_remove_host ? (
          <ItemAction>
            <ButtonInviteStyled
              onClick={onAction}
              size="small"
              variant="outlined"
              disabled={isSubmit}
            >
              {i18n.formatMessage({ id: 'remove_host' })}
            </ButtonInviteStyled>
          </ItemAction>
        ) : null}
      </ItemContent>
    </Root>
  );
}

HostSmallerCard.LoadingSkeleton = LoadingSkeleton;

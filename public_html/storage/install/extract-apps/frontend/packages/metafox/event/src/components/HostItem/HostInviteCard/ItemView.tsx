import { EventDetailViewProps } from '@metafox/event';
import { Link, useGetItem, useGlobal } from '@metafox/framework';
import {
  ItemAction,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  UserAvatar
} from '@metafox/ui';
import { Button, styled } from '@mui/material';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

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
    justifyContent: 'space-between',
    alignItems: 'center',
    width: '100%'
  })
);

const ButtonInviteStyled = styled(Button, { name: 'ButtonInviteStyled' })(
  ({ theme }) => ({
    fontWeight: theme.typography.fontWeightBold
  })
);

export default function HostInviteCard({
  identity,
  user,
  wrapProps,
  wrapAs,
  actions,
  item
}: EventDetailViewProps) {
  const { i18n } = useGlobal();
  const owner = useGetItem(item.owner);
  const [isSubmit, setIsSubmit] = React.useState(false);

  if (!owner) return null;

  const { link: to, full_name } = owner;

  const onAction = () => {
    setIsSubmit(true);
    actions.cancelHostInvitation(() => setIsSubmit(false));
  };

  return (
    <Root
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${user.resource_name}`}
      data-eid={identity}
    >
      <ItemContent>
        <ItemMedia>
          <UserAvatar user={owner} size={48} />
        </ItemMedia>
        <ItemText>
          <ItemTitle>
            <Link to={to} children={full_name} color={'inherit'} />
          </ItemTitle>
        </ItemText>
        <ItemAction>
          <ButtonInviteStyled
            size="small"
            variant="outlined"
            disabled={isSubmit}
            onClick={onAction}
          >
            {i18n.formatMessage({ id: 'cancel_invite' })}
          </ButtonInviteStyled>
        </ItemAction>
      </ItemContent>
    </Root>
  );
}

HostInviteCard.LoadingSkeleton = LoadingSkeleton;

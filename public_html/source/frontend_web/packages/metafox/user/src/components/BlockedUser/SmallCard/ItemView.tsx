import { Link, useGlobal } from '@metafox/framework';
import {
  ItemAction,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  ItemViewProps,
  UserAvatar
} from '@metafox/ui';
import { Button, styled } from '@mui/material';
import React from 'react';

const name = 'ItemViewBlockUser';
const ButtonUnBlock = styled(Button, { name })(({ theme }) => ({
  fontWeight: theme.typography.fontWeightBold,
  height: theme.spacing(4.5),
  padding: theme.spacing(0, 2)
}));
export interface BlockedUserItemShape {
  resource_name: string;
  module_name: string;
  user_name: string;
  full_name: string;
  avatar: string;
  is_featured: boolean;
  id: number;
  is_blocked: boolean;
}
export interface BlockedUserAction {
  unblockItem: any;
}

export default function ItemViewBlockUser({
  item,
  actions,
  wrapAs,
  wrapProps
}: ItemViewProps<BlockedUserItemShape, BlockedUserAction>) {
  const { i18n } = useGlobal();

  if (!item) return null;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={item.id}
    >
      <ItemMedia>
        <UserAvatar user={item} size={80} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Link
            // to={to}
            children={item.full_name}
            color={'inherit'}
            hoverCard={`/user/${item.id}`}
          />
        </ItemTitle>
      </ItemText>
      <ItemAction>
        {item.is_blocked ? (
          <ButtonUnBlock
            variant="outlined"
            size="small"
            color="primary"
            onClick={actions.unblockItem}
          >
            {i18n.formatMessage({ id: 'user_unblock' })}
          </ButtonUnBlock>
        ) : null}
      </ItemAction>
    </ItemView>
  );
}

ItemViewBlockUser.displayName = 'BlockedUser_SmallCard';

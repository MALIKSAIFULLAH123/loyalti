import { Link, useGlobal } from '@metafox/framework';
import { FriendListItemProps } from '@metafox/friend';
import {
  ItemAction,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  Statistic
} from '@metafox/ui';
import { styled } from '@mui/material';
import * as React from 'react';

const ItemWrapperContent = styled('div')(({ theme }) => ({
  flex: 1,
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center'
}));
const WrapperLink = styled(Link)(({ theme }) => ({
  flex: 1,
  display: 'flex',
  '&:hover': {
    textDecoration: 'none !important'
  },
  '&:hover .ItemView-title': {
    textDecoration: 'underline'
  }
}));

const WrapperTitle = styled(ItemTitle)(() => ({
  '& > *': {
    whiteSpace: 'inherit',
    wordBreak: 'break-word'
  }
}));

const FriendListMainItem = ({
  item,
  identity,
  handleAction,
  state,
  itemProps,
  wrapAs,
  wrapProps
}: FriendListItemProps) => {
  const { ItemActionMenu, usePageParams } = useGlobal();
  const { list_id } = usePageParams();

  // eslint-disable-next-line eqeqeq
  const selected = list_id == item.id;

  if (!item) return null;

  const to = `/friend/list/${item.id}`;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      color="inherit"
    >
      <ItemWrapperContent>
        <WrapperLink color={selected ? 'primary' : 'inherit'} to={to}>
          <ItemMedia>
            <LineIcon
              icon="ico-list-bullet"
              sx={{
                fontSize: '1rem',
                color: selected ? 'primary' : 'text.primary'
              }}
            />
          </ItemMedia>
          <ItemText color={selected ? 'primary' : 'text.primary'}>
            <WrapperTitle>{item.name}</WrapperTitle>
            <ItemSummary>
              <Statistic
                values={{ total_friend: item.total_friend }}
                display="total_friend"
                skipZero={false}
              />
            </ItemSummary>
          </ItemText>
        </WrapperLink>
        {itemProps.showActionMenu ? (
          <ItemAction visible="hover">
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              state={state}
              handleAction={handleAction}
            />
          </ItemAction>
        ) : null}
      </ItemWrapperContent>
    </ItemView>
  );
};

export default FriendListMainItem;

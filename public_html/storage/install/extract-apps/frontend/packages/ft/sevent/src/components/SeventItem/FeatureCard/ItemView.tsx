/**
 * @type: itemView
 * name: sevent.itemView.featureCard
 */
import actionCreators from '@ft/sevent/actions/seventItemActions';
import { Link, useGlobal, connectItemView, getItemSelector } from '@metafox/framework';
import { SeventItemProps as ItemProps } from '@ft/sevent/types';
import {
  ItemTitle,
  ItemUserShape,
  UserAvatar,
  UserName,
  CategoryList
} from '@metafox/ui';
import { styled, useTheme, Box } from '@mui/material';
import React from 'react';
import { useSelector } from 'react-redux';

export function FeatureCard({
  item
}: ItemProps) {

  const {
    useGetItems
  } = useGlobal();
  const ItemContent = styled('div', { slot: 'ItemContent' })(
    ({ theme }) => ({
      width: '100%'
    })
  );
  const theme = useTheme();
  const { i18n } = useGlobal();
  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );

  const AvatarWrapper = styled('div', { name: 'AvatarWrapper', slot: 'AvatarWrapper' })(
    ({ theme }) => ({
      marginRight: theme.spacing(1)
    })
  );

  const user = useSelector((state: GlobalState) =>
    getItemSelector(state, item?.user)
  );

  const ProfileLinkStyled = styled(UserName, {
    name: 'ProfileLinkStyled',
    slot: 'profileLink'
  })(({ theme }) => ({
    fontSize: theme.mixins.pxToRem(13),
    paddingRight: theme.spacing(0.5)
  }));

  return (
    <ItemContent>
      <Box sx={{ p: theme.spacing(1) }}>
        <Box display='flex' alignItems='center' style={{ marginBottom: theme.spacing(2) }}>
          <AvatarWrapper>
            <UserAvatar user={user as ItemUserShape} size={32} />
          </AvatarWrapper>
          <ProfileLinkStyled user={user} data-testid="headline" />
          {i18n.formatMessage({ id: 'in' })} 
          <CategoryList style={{ margin: '0 0 0 5px', textTransform: 'initial' }} 
            data={categories} sx={{ mb: 1, mr: 2 }} />
        </Box>
        <ItemTitle>
          <Link style={{ fontWeight:'bold', fontSize: '16px' }} to={item.link}>{item.title}</Link>
        </ItemTitle>
      </Box>
    </ItemContent>
  );
}

export default connectItemView(FeatureCard, actionCreators, {});
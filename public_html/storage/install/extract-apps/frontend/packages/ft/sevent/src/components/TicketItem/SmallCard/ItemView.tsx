/**
 * @type: itemView
 * name: sevent_ticket.itemView.smallCard
 */
import actionCreators from '@ft/sevent/actions/seventItemActions';
import { Link, useGlobal, connectItemView } from '@metafox/framework';
import { SeventItemProps as ItemProps } from '@ft/sevent/types';
import {
  ItemText,
  ItemTitle,
  CategoryList
} from '@metafox/ui';
import { styled, useTheme, Box } from '@mui/material';
import { getImageSrc } from '@metafox/utils';
import React from 'react';
import PeopleIcon from '@mui/icons-material/People';

export function SeventItemSmallCard({
  item
}: ItemProps) {

  const {
    assetUrl,
    useGetItems
  } = useGlobal();
  const ItemContent = styled('div', { slot: 'ItemContent' })(
    ({ theme }) => ({
      width: '100%',
      marginBottom: '20px'
    })
  );
  const theme = useTheme();
  const { link: to } = item;
  const cover = (item?.image ? getImageSrc(item?.image) : assetUrl('sevent.no_image'));
  
  const Image = styled('div', { slot: 'ItemContent' })(
    ({ theme }) => ({
    width: '100%',
    height: '140px',
    background: 'url("' + cover + '")',
    backgroundSize: 'cover',
    marginBottom: '8px'
    })
  );

  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );

  return (
    <ItemContent>
      <a href={to}>
        <Image/>
      </a>
      <ItemText>
        <ItemTitle>
          <Link style={{ fontWeight:'bold', fontSize: '16px' }} to={item.link}>{item.title}</Link>
        </ItemTitle>
        <Box display='flex' alignItems='center' sx={{ mt: '5px' }}>
          <CategoryList style={{ margin: '0 8px 0 0', color: theme.palette.text.secondary }} 
          data={categories} sx={{ mb: 1, mr: 2 }} />
          <PeopleIcon fontSize={'small'}></PeopleIcon> 
          <div style={{ marginLeft: '5px' }}>{item.statistic.total_play}</div>
        </Box>
      </ItemText>
    </ItemContent>
  );
}

export default connectItemView(SeventItemSmallCard, actionCreators, {});
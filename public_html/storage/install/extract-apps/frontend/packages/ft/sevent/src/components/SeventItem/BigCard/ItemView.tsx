/**
 * @type: itemView
 * name: sevent.itemView.bigCard
 */
import actionCreators from '@ft/sevent/actions/seventItemActions';
// types
import { SeventItemProps as ItemProps } from '@ft/sevent/types';
import { connectItemView, Link, useGlobal } from '@metafox/framework';
// components
import {
  CategoryList,
  FormatDate,
  ItemSubInfo,
  ItemSummary,
  ItemView,
  ItemText,
  ItemTitle,
  UserAvatar,
  Statistic,
  UserName
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { styled, Tooltip, Box, useTheme } from '@mui/material';
import React from 'react';

const name = 'SeventItemView';
const CategoryStyled = styled(CategoryList, {
  slot: 'Category',
  name,
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile: boolean }>(({ theme, isMobile }) => ({
  ...(isMobile && {
  })
}));
const ItemTitleStyled = styled(ItemTitle, {
  name,
  slot: 'ItemTitleStyled',
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile: boolean }>(({ theme, isMobile }) => ({
  '& h4': {
    height: 'auto',
    maxHeight: '100%'
  }
}));

export function SeventItemView({
  identity,
  itemProps,
  item,
  user,
  wrapProps,
  wrapAs,
  state,
  handleAction
}: ItemProps) {
  const theme = useTheme();
  const {
    useIsMobile,
    useGetItems,
    InViewTrackingSponsor,
    assetUrl
  } = useGlobal();
  const isMobile = useIsMobile();
  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );
  const { i18n } = useGlobal();

  if (!item || !user) return null;

  const { link: to, creation_date, is_sponsor } = item;

  const cover = (item?.image ? getImageSrc(item?.image) : assetUrl('sevent.no_image'));

  const isTrackingViewSponsor =
    is_sponsor && itemProps?.isTrackingSponsor && InViewTrackingSponsor;

  const AvatarWrapper = styled('div', { name: 'AvatarWrapper', slot: 'AvatarWrapper' })(
      ({ theme }) => ({
        marginRight: theme.spacing(1)
      })
    );
  const ItemViewBox = styled('div', { name: 'ItemViewBox', slot: 'ItemViewBox' })(
      ({ theme }) => ({
        display: 'flex',
        width: '100%',
        flexDirection: 'column',
        marginBottom: theme.spacing(3)
      })
    );
  const ImgMedia = styled('div', { name: 'ImgMedia', slot: 'ItemViewBox' })(
      ({ theme }) => ({ 
        width: '100%', 
        height: '162px', 
        background: 'url(' + cover + ')', 
        backgroundSize: 'cover',
        backgroundPosition: 'center'
      })
    );

  const ItemTextStyled = styled(ItemText, { name: 'ItemTextStyled', slot: 'ItemViewBox' })(
      ({ theme }) => ({
        paddingLeft: 0,
        paddingRight: 0,
        [theme.breakpoints.down('sm')]: {
          paddingLeft: 0,
          paddingRight: 0,
          width: '100%'
        }
      })
    );
   
  return (
  <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="blog" sx={{ br: 0 }}>
    <ItemViewBox>
      {isTrackingViewSponsor ? (
        <InViewTrackingSponsor identity={identity} />
      ) : null}
      <Link href={to}><ImgMedia></ImgMedia></Link>
      <ItemTextStyled>
        <Box sx={{ margin: theme.spacing(1, 0) }} 
          display='flex' gap='4px' alignItems='center'>
            <AvatarWrapper>
              <UserAvatar user={user as ItemUserShape} size={32} />
            </AvatarWrapper>
            <UserName color="inherit" to={user.link} user={user} />
            {i18n.formatMessage({ id: 'in' })} 
            <CategoryStyled isMobile={isMobile} data={categories} 
              style={{ textTransform: 'initial', paddingRight: '4px' }} />
          </Box>
          <ItemTitleStyled lines={3} isMobile={isMobile}>
            <Tooltip title={item.title} arrow>
                <Link href={to} style={{ fontSize: '1.125rem', 
                lineHeight: '1.33', fontWeight: 'bold' }}><div>{item.title}</div></Link>
            </Tooltip>
        </ItemTitleStyled>
        <ItemSummary style={{ margin: theme.spacing(2, 0), lineHeight: '20px',
          color: theme.palette.text.secondary
        }} lines={2}>{item.description}</ItemSummary>
        <ItemSubInfo sx={{ color: 'text.secondary', marginBottom: theme.spacing(2) }}>
          <Statistic
              values={item.statistic}
              display={'total_view'}
              component={'span'}
              skipZero={false}
            />
            <span>{item.time_to_read} {i18n.formatMessage({ id: 'time_to_read' })} </span>
            <FormatDate
            data-testid="creationDate"
            value={creation_date}
            format="ll"
          />
        </ItemSubInfo>
      </ItemTextStyled>
    </ItemViewBox>
    </ItemView>
  );
}

export default connectItemView(SeventItemView, actionCreators, {});

/**
 * @type: itemView
 * name: sevent.itemView.sponsorCard
 */
import actionCreators from '@ft/sevent/actions/seventItemActions';
import { SeventItemProps as ItemProps } from '@ft/sevent/types';
import { connectItemView, Link, useGlobal } from '@metafox/framework';
import { getImageSrc } from '@metafox/utils';
import {
  CategoryList,
  DraftFlag,
  FeaturedFlag,
  ItemAction,
  ItemView,
  ItemText,
  ItemTitle,
  UserAvatar,
  PendingFlag,
  SponsorFlag,
  UserName
} from '@metafox/ui';
import { styled, Tooltip, Box, useTheme } from '@mui/material';
import React from 'react';

const name = 'SeventItemView';
const FlagWrapper = styled('span', {
  slot: 'FlagWrapper',
  name
})(({ theme }) => ({
  display: 'inline-flex'
}));
const SeventDescription = styled('div', {
  name: 'SeventDescription'
})(({ theme }) => ({
  fontSize: '14px',
  lineHeight: '22px',
  color: theme.palette.text.secondary,
  marginTop: theme.spacing(1),
  marginBottom: theme.spacing(2)
}));
const CategoryStyled = styled(CategoryList, {
  slot: 'Category',
  name,
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile: boolean }>(({ theme, isMobile }) => ({
  disply: 'inline !important',
  ...(isMobile && {
  }),
  '&:after' : {
    content: '"·"',
    paddingLeft: '0.4em',
    paddingRight: '0.4em',
    fontWeight: 'normal'
  }
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
  wrapProps,
  wrapAs,
  item,
  user,
  state,
  handleAction
}: ItemProps) {
  const theme = useTheme();
  const {
    ItemActionMenu,
    useIsMobile,
    useGetItems,
    usePageParams,
    LinkTrackingSponsor,
    InViewTrackingSponsor
  } = useGlobal();
  const { tab } = usePageParams();
  const isMobile = useIsMobile();
  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );
  const { i18n, assetUrl } = useGlobal();

  if (!item || !user) return null;

  const { link: to, is_sponsor } = item;

  const isTrackingViewSponsor =
    is_sponsor && itemProps?.isTrackingSponsor && InViewTrackingSponsor;
  
  const isTrackingClickSponsor =
    is_sponsor && itemProps?.isTrackingSponsor && LinkTrackingSponsor;
  const AvatarWrapper = styled('div', { name: 'AvatarWrapper', slot: 'AvatarWrapper' })(
      ({ theme }) => ({
        marginRight: theme.spacing(1)
      })
    );
  const ItemViewBox = styled('div', { name: 'ItemViewBox', slot: 'ItemViewBox' })(
      ({ theme }) => ({
        display: 'flex',
        width: '100%',
        border:'1px dotted' + 
        (theme.palette.mode === 'light'
          ? theme.palette.background.default
          : theme.palette.action.hover),
        alignItems: 'center',
        justifyContent: 'space-between'
      })
    );

  const cover = (item?.image ? getImageSrc(item?.image) : assetUrl('sevent.no_image'));
  const ImgMedia = styled('div', { name: 'ImgMedia', slot: 'ItemViewBox' })(
      ({ theme }) => ({ 
        width: '112px', 
        height: '112px', 
        marginRight: theme.spacing(2),
        background: 'url(' + cover + ')', 
        backgroundSize: 'cover',
        backgroundPosition: 'center' 
      })
    );
    
  const ItemTextStyled = styled(ItemText, { name: 'ItemTextStyled', slot: 'ItemViewBox' })(
      ({ theme }) => ({
        paddingBottom: 0,
        [theme.breakpoints.down('sm')]: {
          width: '100%'
        }
      })
    );

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="blog" 
      style={{ borderRadius: 0 }}>
    <ItemViewBox>
      {isTrackingViewSponsor ? (
        <InViewTrackingSponsor identity={identity} />
      ) : null}
      <ItemTextStyled>
        <Box sx={{ color: 'text.secondary', marginBottom: theme.spacing(1) }} 
          display='flex' gap='4px' flexWrap='wrap' width="80%" alignItems='center'>
            <AvatarWrapper>
              <UserAvatar user={user as ItemUserShape} size={32} />
            </AvatarWrapper>
            <UserName color="inherit" to={user.link} user={user} />
            {i18n.formatMessage({ id: 'in' })} 
            <CategoryStyled isMobile={isMobile} data={categories} style={{
              color: theme.palette.text.secondary,
              textTransform: 'initial' }} />
            <span>{item.time_to_read} {i18n.formatMessage({ id: 'time_to_read' })} </span>
          </Box>
        <ItemTitleStyled isMobile={isMobile}>
          <FlagWrapper >
            <FeaturedFlag variant="itemView" value={item.is_featured} />
            <SponsorFlag
              variant="itemView"
              value={item.is_sponsor}
              item={item}
            />
            <PendingFlag variant="itemView" value={item.is_pending} />
          </FlagWrapper>
          <DraftFlag
            sx={{ fontWeight: 'normal' }}
            value={item.is_draft && tab !== 'draft'}
            variant="h4"
            component="span"
          />
          <Tooltip title={item.title} arrow>
            {isTrackingClickSponsor ? (
              <LinkTrackingSponsor to={item.link} identity={identity}>
                {item.title}
              </LinkTrackingSponsor>
            ) : (
              <Link to={item.link}>{item.title}</Link>
            )}
          </Tooltip>
        </ItemTitleStyled> 
        <SeventDescription>{!isMobile ? item.description : item.short_description}</SeventDescription>
      </ItemTextStyled>
      {!isMobile ? 
        (<a href={to}><ImgMedia></ImgMedia></a>)
        : null }
      {itemProps.showActionMenu ? (
          <ItemAction
            placement={'top-end'}
            spacing="normal"
            style={{ marginRight: theme.spacing(1) }}
          >
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-o'}
              state={state}
              handleAction={handleAction}
            />
          </ItemAction>
        ) : null}
    </ItemViewBox>
    </ItemView>
  );
}

export default connectItemView(SeventItemView, actionCreators, {});

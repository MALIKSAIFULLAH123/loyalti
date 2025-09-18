/**
 * @type: itemView
 * name: sevent.itemView.profileCard
 */
import actionCreators from '@ft/sevent/actions/seventItemActions';
// types
import { SeventItemProps as ItemProps } from '@ft/sevent/types';
import { connectItemView, Link, useGlobal } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
// components
import {
  CategoryList,
  DraftFlag,
  FeaturedFlag,
  FormatDate,
  ItemAction,
  ItemMedia,
  ItemSubInfo,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  PendingFlag,
  SponsorFlag,
  Statistic
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { styled, Tooltip } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';

const name = 'SeventItemView';

const FlagWrapper = styled('span', {
  slot: 'FlagWrapper',
  name,
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile: boolean }>(({ theme, isMobile }) => ({
  display: 'inline-flex',
  ...(isMobile && {
    marginTop: theme.spacing(1)
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
  state,
  handleAction,
  wrapAs,
  wrapProps
}: ItemProps) {
  const {
    ItemActionMenu,
    useIsMobile,
    useGetItems,
    usePageParams,
    LinkTrackingSponsor,
    InViewTrackingSponsor,
    assetUrl
  } = useGlobal();
  const { tab } = usePageParams();
  const isMobile = useIsMobile();
  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );
  const { itemLinkProps = {} } = useBlock();

  if (!item || !user) return null;

  const { link: to, creation_date, is_sponsor } = item;

  const cover = (item?.image ? getImageSrc(item?.image) : (item?.thumb ? item.thumb : assetUrl('sevent.no_image')));

  const isTrackingViewSponsor =
    is_sponsor && itemProps?.isTrackingSponsor && InViewTrackingSponsor;
  const isTrackingClickSponsor =
    is_sponsor && itemProps?.isTrackingSponsor && LinkTrackingSponsor;

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="sevent">
      {isTrackingViewSponsor ? (
        <InViewTrackingSponsor identity={identity} />
      ) : null}
      <ItemMedia
        asModal={itemLinkProps.asModal}
        src={cover}
        link={to}
        alt={item.title}
        backgroundImage
      />
      <ItemText>
        <CategoryList data={categories} sx={{ mb: { sm: 1, xs: 0 } }} />
        <ItemTitleStyled isMobile={isMobile}>
          <FlagWrapper isMobile={isMobile}>
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
        {itemProps.showActionMenu ? (
          <ItemAction
            placement={isMobile ? 'bottom-end' : 'top-end'}
            spacing="normal"
          >
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              state={state}
              handleAction={handleAction}
            />
          </ItemAction>
        ) : null}
        {isEmpty(item.description) ? null : (
          <ItemSummary sx={{ my: 1 }}>{item.description}</ItemSummary>
        )}
        <ItemSubInfo sx={{ color: 'text.secondary' }}>
          <FormatDate
            data-testid="creationDate"
            value={creation_date}
            format="ll"
          />
          <Statistic
            values={item.statistic}
            display={'total_view'}
            component={'span'}
            skipZero={false}
          />
        </ItemSubInfo>
      </ItemText>
    </ItemView>
  );
}

export default connectItemView(SeventItemView, actionCreators, {});

/**
 * @type: itemView
 * name: blog.itemView.mainCard
 * chunkName: blog
 */
import actionCreators from '@metafox/blog/actions/blogItemActions';
// types
import { BlogItemProps as ItemProps } from '@metafox/blog/types';
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
  Statistic,
  UserName
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { styled, Tooltip } from '@mui/material';
import HtmlViewer from '@metafox/html-viewer';
import React from 'react';

const name = 'BlogItemView';

const FlagWrapper = styled('span', {
  slot: 'FlagWrapper',
  name
})(({ theme }) => ({
  display: 'inline-flex'
}));

const CategoryStyled = styled(CategoryList, {
  slot: 'Category',
  name,
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile: boolean }>(({ theme, isMobile }) => ({
  ...(isMobile && {
    marginBottom: `${theme.spacing(1)} !important`
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

export function BlogItemView({
  identity,
  itemProps,
  item,
  user,
  state,
  handleAction,
  wrapAs,
  wrapProps
}: ItemProps) {
  const { ItemActionMenu, useIsMobile, useGetItems, usePageParams } =
    useGlobal();
  const { tab } = usePageParams();
  const isMobile = useIsMobile();
  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );
  const { itemLinkProps = {} } = useBlock();

  if (!item || !user) return null;

  const { link: to, creation_date } = item || {};

  const cover = getImageSrc(item?.image, '240');

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid="blog"
      identity={identity}
    >
      <ItemMedia
        asModal={itemLinkProps.asModal}
        src={cover}
        link={to}
        alt={item.title}
        backgroundImage
      />
      <ItemText>
        <CategoryStyled
          isMobile={isMobile}
          data={categories}
          sx={{ mb: { sm: 1, xs: 0 } }}
        />
        <ItemTitleStyled isMobile={isMobile}>
          <FlagWrapper>
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
            <Link to={item.link} identityTracking={identity}>
              {item.title}
            </Link>
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
        {item.description ? (
          <ItemSummary sx={{ my: 1 }}>
            <HtmlViewer html={item.description} simpleTransform />
          </ItemSummary>
        ) : null}
        <ItemSubInfo sx={{ color: 'text.secondary' }}>
          <UserName color="inherit" to={user.link} user={user} />
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

export default connectItemView(BlogItemView, actionCreators, {});

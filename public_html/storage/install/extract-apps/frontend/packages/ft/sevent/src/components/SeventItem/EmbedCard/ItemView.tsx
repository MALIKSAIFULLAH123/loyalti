/**
 * @type: embedView
 * name: sevent.embedItem.insideFeedItem
 */
import { EmbedSeventItemInFeedItemProps } from '@ft/sevent';
import actionCreators from '@ft/sevent/actions/seventItemActions';
import { connectItemView, Link, useGlobal } from '@metafox/framework';
import {
  FeaturedFlag,
  FeedEmbedCard,
  FeedEmbedCardMedia,
  SponsorFlag,
  Statistic,
  TruncateText
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { styled } from '@mui/material';
import * as React from 'react';

const name = 'SeventItemEmbedCard';
const ItemContent = styled('div', { name, slot: 'itemInner' })(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  padding: theme.spacing(2),
  display: 'flex',
  flexDirection: 'column'
}));
const WrapperInfoFlag = styled('div', { name, slot: 'wrapperInfoFlag' })(
  ({ theme }) => ({
    marginTop: 'auto',
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'flex-end'
  })
);
const FlagWrapper = styled('span', {
  name,
  slot: 'flagWrapper'
})(({ theme }) => ({
  marginLeft: 'auto',
  '& > .MuiFlag-root': {
    marginLeft: theme.spacing(2.5)
  }
}));

export function SeventEmbedCard({
  feed,
  item,
  isShared
}: EmbedSeventItemInFeedItemProps) {
  const { LinkTrackingSponsor, assetUrl } = useGlobal();

  if (!item) return null;

  const link = item.link || feed.link;
  const isSponsored = feed?.is_sponsor;
  const trackingSponsor = isSponsored && LinkTrackingSponsor;
  const cover = (item?.image ? getImageSrc(item?.image) : (item?.thumb ? item.thumb : assetUrl('sevent.no_image')));
  
  return (
    <FeedEmbedCard
      bottomSpacing="normal"
      item={item}
      feed={feed}
      isShared={isShared}
    >
      {cover ? (
        <FeedEmbedCardMedia image={cover} mediaRatio="11" link={link} />
      ) : null}
      <ItemContent>
        <TruncateText variant="h4" lines={2} sx={{ mb: 1 }}>
          {trackingSponsor ? (
            <LinkTrackingSponsor to={link} identity={feed?._identity}>
              {item.title}
            </LinkTrackingSponsor>
          ) : (
            <Link to={link}>{item.title}</Link>
          )}
        </TruncateText>
        <TruncateText
          variant={'body1'}
          lines={3}
          component="div"
          sx={{ mb: 2, color: 'text.secondary' }}
        >
          <div dangerouslySetInnerHTML={{ __html: item.description }} />
        </TruncateText>
        <WrapperInfoFlag>
          <div>
            <Statistic
              values={item.statistic}
              display="total_view"
              fontStyle="minor"
              skipZero={false}
            />
          </div>
          <FlagWrapper>
            <FeaturedFlag
              variant="text"
              value={item.is_featured}
              color="primary"
              showTitleMobile={false}
            />
            <SponsorFlag
              color="yellow"
              variant="text"
              value={item.is_sponsor}
              showTitleMobile={false}
              item={item}
            />
          </FlagWrapper>
        </WrapperInfoFlag>
      </ItemContent>
    </FeedEmbedCard>
  );
}
SeventEmbedCard.displayName = 'SeventEmbedCard';

export default connectItemView(SeventEmbedCard, actionCreators);

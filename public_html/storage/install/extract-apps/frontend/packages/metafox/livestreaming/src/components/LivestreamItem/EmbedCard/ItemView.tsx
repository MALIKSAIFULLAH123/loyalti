import { Link, useGlobal } from '@metafox/framework';
import {
  FeaturedFlag,
  SponsorFlag,
  Statistic,
  TruncateText,
  ItemMedia,
  FeedEmbedCard
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { LivestreamItemShape } from '@metafox/livestreaming';
import { Box, styled } from '@mui/material';
import * as React from 'react';

const name = 'LivestreamEmbedView';

const WrapperInfoFlag = styled('div', { name, slot: 'wrapperInfoFlag' })(
  ({ theme }) => ({
    marginTop: 'auto',
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'flex-end'
  })
);

const ItemInner = styled(Box, {
  name,
  slot: 'ItemInner'
})(({ theme }) => ({
  padding: theme.spacing(2),
  display: 'flex',
  flexDirection: 'column'
}));

const FlagWrapper = styled('span', {
  name,
  slot: 'flagWrapper'
})(({ theme }) => ({
  marginLeft: 'auto',
  '& > .MuiFlag-root': {
    marginLeft: theme.spacing(2.5),
    [theme.breakpoints.down('sm')]: {
      marginLeft: theme.spacing(0.5)
    }
  }
}));

export default function LivestreamEmbedView({
  item,
  feed,
  isShared
}: {
  item: LivestreamItemShape;
  feed: Record<string, any>;
  isShared?: boolean;
}) {
  const { jsxBackend, useSession } = useGlobal();
  const { title, statistic, is_streaming, thumbnail_url } = item || {};
  const cover = getImageSrc(thumbnail_url, '500', '');
  const MediaLayer = jsxBackend.get('livestreaming.ui.overlayVideo');
  const { loggedIn } = useSession();

  return (
    <FeedEmbedCard
      bottomSpacing="normal"
      item={item}
      feed={feed}
      isShared={isShared}
      sxOuter={{
        overflow: 'visible',
        borderRadius: 0,
        borderWidth: 0,
        borderBottomWidth: 1,
        margin: theme => `0 ${theme.spacing(-2)}`
      }}
      sx={{ paddingTop: 0 }}
    >
      <Box sx={{ width: '100%' }} pb={loggedIn ? 0 : 2}>
        <Box>
          <ItemMedia src={cover} backgroundImage>
            <MediaLayer item={item} />
          </ItemMedia>
        </Box>
        <ItemInner>
          {title ? (
            <Box mb={1} fontWeight={600}>
              <Link to={item.link} asModal identityTracking={feed?._identity}>
                <TruncateText variant="h4" lines={1}>
                  {title}
                </TruncateText>
              </Link>
            </Box>
          ) : null}
          <WrapperInfoFlag>
            {!is_streaming ? (
              <Statistic
                values={statistic}
                display="total_view"
                fontStyle="minor"
                skipZero={false}
              />
            ) : null}
            <FlagWrapper>
              <FeaturedFlag
                variant="text"
                value={item?.is_featured}
                color="primary"
                showTitleMobile={false}
              />
              <SponsorFlag
                color="yellow"
                variant="text"
                value={item?.is_sponsor}
                showTitleMobile={false}
                item={item}
              />
            </FlagWrapper>
          </WrapperInfoFlag>
        </ItemInner>
      </Box>
    </FeedEmbedCard>
  );
}

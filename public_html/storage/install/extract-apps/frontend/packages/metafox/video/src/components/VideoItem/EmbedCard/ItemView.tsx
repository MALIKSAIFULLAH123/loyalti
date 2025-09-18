import { useGlobal } from '@metafox/framework';
import {
  FeaturedFlag,
  Image,
  SponsorFlag,
  Statistic,
  TruncateText,
  FeedEmbedCard
} from '@metafox/ui';
import VideoPlayer from '@metafox/ui/VideoPlayer';
import { getImageSrc } from '@metafox/utils';
import { VideoItemShape } from '@metafox/video';
import { Box, styled } from '@mui/material';
import * as React from 'react';
import { MatureLink } from '@metafox/video/components';

const name = 'VideoEmbedView';

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
    marginLeft: theme.spacing(2.5),
    [theme.breakpoints.down('sm')]: {
      marginLeft: theme.spacing(0.5)
    }
  }
}));

export default function VideoEmbedView({
  item,
  feed,
  isShared
}: {
  item: VideoItemShape;
  feed: Record<string, any>;
}) {
  const { assetUrl, useSession } = useGlobal();
  const { loggedIn } = useSession();

  if (!item) return null;

  const src = item.video_url || item.destination;

  const identity = item?._identity;

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
          {item?.is_processing ? (
            <div>
              <Image
                src={getImageSrc(
                  null,
                  '1024',
                  assetUrl('video.video_in_processing_image')
                )}
                aspectRatio={'169'}
              />
            </div>
          ) : (
            <VideoPlayer
              src={src}
              embed_code={item?.embed_code}
              thumb_url={item.image}
              autoplayIntersection
              detailLink={item.link}
              isCountVideo
              id={item.id}
              // modalUrl={item.link}
              identity={identity}
            />
          )}
        </Box>
        <Box p={2} data-testid="embedview">
          <Box mb={1} fontWeight={600}>
            <MatureLink
              to={item.link}
              asModal
              identityTracking={feed?._identity}
              identity={identity}
            >
              <TruncateText variant="h4" lines={1}>
                {item.title}
              </TruncateText>
            </MatureLink>
          </Box>
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
                value={item?.is_featured}
                color="primary"
                showTitleMobile={false}
              />
              <SponsorFlag
                color="yellow"
                variant="text"
                value={item?.is_sponsor}
                item={item}
                showTitleMobile={false}
              />
            </FlagWrapper>
          </WrapperInfoFlag>
        </Box>
      </Box>
    </FeedEmbedCard>
  );
}

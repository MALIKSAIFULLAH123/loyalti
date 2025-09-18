import { Link, useGlobal } from '@metafox/framework';
import { AlbumItemShape } from '@metafox/music';
import {
  FeaturedFlag,
  FeedEmbedCard,
  FeedEmbedCardMedia,
  FeedEmbedCardMediaProps,
  FeedEmbedCardProps,
  Statistic,
  TruncateText
} from '@metafox/ui';
import { Box, styled } from '@mui/material';
import { getImageSrc } from '@metafox/utils';
import * as React from 'react';
import HtmlViewer from '@metafox/html-viewer';

const FlagWrapper = styled('span', {
  slot: 'flagWrapper'
})(({ theme }) => ({
  marginLeft: 'auto',
  '& > .MuiFlag-root': {
    marginLeft: theme.spacing(2.5)
  }
}));

const ItemInner = styled('div', {
  slot: 'ItemInner'
})(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  padding: theme.spacing(3),
  display: 'flex',
  flexDirection: 'column'
}));

const WrapperInfoFlag = styled(Box, {
  slot: 'WrapperInfoFlag'
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-end',
  marginTop: 'auto'
}));

const Title = styled(Box, {
  slot: 'Title'
})(({ theme }) => ({
  marginBottom: theme.spacing(1),
  fontWeight: 'bold',
  '& a': {
    color: theme.palette.text.primary
  }
}));

type EmbedAlbumProps = {
  item: AlbumItemShape;
  user: any;
} & FeedEmbedCardMediaProps &
  FeedEmbedCardProps;

const EmbedMusicPlaylistItem = ({
  item,
  feed,
  isShared,
  variant = 'list'
}: EmbedAlbumProps) => {
  const { assetUrl } = useGlobal();

  if (!item) return null;

  return (
    <FeedEmbedCard
      variant={variant}
      bottomSpacing="normal"
      item={item}
      feed={feed}
      isShared={isShared}
    >
      <FeedEmbedCardMedia
        image={getImageSrc(
          item.image,
          '240',
          assetUrl('music.playlist_no_image')
        )}
        widthImage="200px"
      />
      <ItemInner>
        <Title>
          <Link to={item.link}>
            <TruncateText variant="h4" lines={2}>
              {item.name}
            </TruncateText>
          </Link>
        </Title>
        <Box mb={1}>
          <Statistic
            values={item.statistic}
            display="total_song"
            fontStyle="minor"
            skipZero={false}
          />
        </Box>
        {item.description ? (
          <TruncateText
            variant={'body1'}
            lines={2}
            component="div"
            sx={{ mb: 2, color: 'text.secondary' }}
          >
            <HtmlViewer html={item.description || ''} />
          </TruncateText>
        ) : null}
        <WrapperInfoFlag>
          <FlagWrapper>
            <FeaturedFlag
              variant="text"
              value={item.is_featured}
              color="primary"
              showTitleMobile={false}
            />
          </FlagWrapper>
        </WrapperInfoFlag>
      </ItemInner>
    </FeedEmbedCard>
  );
};

export default EmbedMusicPlaylistItem;

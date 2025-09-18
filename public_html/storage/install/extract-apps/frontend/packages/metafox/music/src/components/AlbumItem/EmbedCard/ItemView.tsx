import { Link, useGlobal } from '@metafox/framework';
import { AlbumItemShape } from '@metafox/music';
import {
  FeaturedFlag,
  FeedEmbedCard,
  FeedEmbedCardMedia,
  FeedEmbedCardMediaProps,
  FeedEmbedCardProps,
  SponsorFlag,
  Statistic,
  TruncateText,
  UserName
} from '@metafox/ui';
import { Box, styled } from '@mui/material';
import { getImageSrc } from '@metafox/utils';
import * as React from 'react';

const FlagWrapper = styled('span', {
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

const Author = styled(Box, {
  slot: 'Author'
})(({ theme }) => ({
  color: theme.palette.text.secondary
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

const Year = styled(Box, {
  slot: 'Year'
})(({ theme }) => ({
  color: theme.palette.text.secondary,
  marginBottom: theme.spacing(1)
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

const EmbedMusicAlbumItem = ({
  item,
  feed,
  user,
  isShared,
  variant = 'list'
}: EmbedAlbumProps) => {
  const { i18n, assetUrl, useIsMobile } = useGlobal();
  const isMobile = useIsMobile();

  if (!item || !user) return null;

  const image = getImageSrc(
    item.image,
    isMobile ? '500' : '240',
    assetUrl('music.song_no_image')
  );

  return (
    <FeedEmbedCard
      variant={variant}
      bottomSpacing="normal"
      item={item}
      feed={feed}
      isShared={isShared}
    >
      <FeedEmbedCardMedia image={image} widthImage="200px" />
      <ItemInner>
        <Title>
          <Link to={item.link}>
            <TruncateText variant="h4" lines={2}>
              {item.name}
            </TruncateText>
          </Link>
        </Title>
        {item.year ? (
          <Year>
            {i18n.formatMessage({ id: 'year' })}: {item.year}
          </Year>
        ) : null}
        <Box mb={1}>
          <Statistic
            values={item.statistic}
            display="total_song"
            fontStyle="minor"
            skipZero={false}
          />
        </Box>
        <Author mb={1}>
          <UserName color="inherit" to={user.link} user={user} />
        </Author>
        <WrapperInfoFlag>
          <FlagWrapper>
            <FeaturedFlag
              variant="text"
              value={item.is_featured}
              color="primary"
              showTitleMobile={false}
            />
            <SponsorFlag
              variant="text"
              value={item.is_sponsor}
              color="yellow"
              showTitleMobile={false}
              item={item}
            />
          </FlagWrapper>
        </WrapperInfoFlag>
      </ItemInner>
    </FeedEmbedCard>
  );
};

export default EmbedMusicAlbumItem;

import { Link, useGetItems, useGetItem, useGlobal } from '@metafox/framework';
import { SongItemShape } from '@metafox/music';
import {
  FeedEmbedCard,
  FeedEmbedCardMedia,
  Statistic,
  TruncateText,
  FeaturedFlag,
  MoreOthers,
  SponsorFlag
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled, Theme, Typography } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import React from 'react';

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

const ValueStyled = styled(Typography, {
  slot: 'value'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.text.secondary,
  fontWeight: theme.typography.fontWeightBold,
  marginLeft: theme.spacing(0.5)
}));

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      item: {
        display: 'block'
      },
      itemOuter: {
        display: 'flex',
        borderRadius: theme.shape.borderRadius,
        border: theme.mixins.border('secondary'),
        backgroundColor: theme.mixins.backgroundColor('paper'),
        overflow: 'hidden'
      },
      title: {
        '& a': {
          color: theme.palette.text.primary
        }
      },
      hostLink: {
        color: theme.palette.text.secondary
      },
      subInfo: {},
      itemInner: {
        flex: 1,
        minWidth: 0,
        padding: theme.spacing(3),
        display: 'flex',
        flexDirection: 'column'
      },
      wrapperInfoFlag: {
        marginTop: 'auto'
      },
      flagWrapper: {
        marginLeft: 'auto'
      },
      information: {
        color: theme.palette.text.secondary
      },
      category: {
        display: 'flex',
        alignItems: 'center'
      },
      album: {}
    }),
  { name: 'MuiFeedEmbedSongCard' }
);

const EmbedMusicSongItem = ({
  item,
  feed,
  isShared
}: {
  item: SongItemShape;
  feed: Record<string, any>;
}) => {
  const categories = useGetItems<{ id: number; name: string }>(item?.genres);
  const album = useGetItem(item?.album);

  const classes = useStyles();
  const { assetUrl, i18n, useIsMobile } = useGlobal();
  const isMobile = useIsMobile();

  if (!item) return;

  return (
    <FeedEmbedCard
      bottomSpacing="normal"
      item={item}
      feed={feed}
      isShared={isShared}
    >
      <Link to={item.link} identityTracking={feed?._identity}>
        <FeedEmbedCardMedia
          image={getImageSrc(
            item.image,
            isMobile ? '500' : '240',
            assetUrl('music.song_no_image')
          )}
          widthImage="200px"
          mediaRatio="11"
        />
      </Link>
      <div className={classes.itemInner}>
        <Box mb={1.5} fontWeight="bold" className={classes.title}>
          <Link to={item.link} identityTracking={feed?._identity}>
            <TruncateText variant="h4" lines={2}>
              {item.name}
            </TruncateText>
          </Link>
        </Box>
        <div className={classes.information}>
          {categories ? (
            <Box className={classes.category} mb={1.5}>
              {i18n.formatMessage({ id: 'genre' })}:
              <ValueStyled>
                <MoreOthers data={categories} />
              </ValueStyled>
            </Box>
          ) : null}
          {album ? (
            <Box className={classes.album}>
              <span>{`${i18n.formatMessage({ id: 'album' })}: `}</span>
              <Link
                to={album?.link}
                children={album?.name}
                color="inherit"
                sx={{ fontWeight: 'bold' }}
              />
            </Box>
          ) : null}
        </div>
        <Box
          className={classes.wrapperInfoFlag}
          display="flex"
          justifyContent="space-between"
          alignItems="flex-end"
        >
          <Statistic
            values={item.statistic}
            display="music_song_total_play"
            fontStyle="minor"
            skipZero={false}
          />
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
        </Box>
      </div>
    </FeedEmbedCard>
  );
};

export default EmbedMusicSongItem;

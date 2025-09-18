import { Link, useGlobal, useGetItem, useGetItems } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent } from '@metafox/layout';
import { SongDetailViewProps } from '@metafox/music/types';
import {
  FormatDate,
  Image,
  LineIcon,
  UserAvatar,
  FeaturedFlag,
  SponsorFlag,
  DotSeparator,
  PrivacyIcon,
  UserName,
  HtmlViewerWrapper
} from '@metafox/ui';
import { Box, styled } from '@mui/material';
import * as React from 'react';
import AudioPlayer, { RHAP_UI } from 'react-h5-audio-player';
import 'react-h5-audio-player/lib/styles.css';
import useStyles from './styles';
import { getImageSrc } from '@metafox/utils';
import AttachmentFile from '@metafox/music/components/Attachment/attachment';
import ProfileLink from '@metafox/feed/components/FeedItemView/ProfileLink';

const HeadlineSpan = styled('span', { name: 'HeadlineSpan' })(({ theme }) => ({
  paddingRight: theme.spacing(0.5),
  color: theme.palette.text.secondary
}));

const OwnerStyled = styled(ProfileLink, { name: 'OwnerStyled' })(
  ({ theme }) => ({
    fontWeight: theme.typography.fontWeightBold,
    color: theme.palette.text.primary,
    fontSize: theme.mixins.pxToRem(15),
    '&:hover': {
      textDecoration: 'underline'
    }
  })
);
const TagItem = styled('div', { name: 'tagItem' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  fontWeight: theme.typography.fontWeightBold,
  borderRadius: theme.shape.borderRadius / 2,
  background:
    theme.palette.mode === 'light'
      ? theme.palette.background.default
      : theme.palette.action.hover,
  marginRight: theme.spacing(1),
  marginBottom: theme.spacing(1),
  padding: theme.spacing(0, 1.5),
  height: theme.spacing(3),
  lineHeight: theme.spacing(3),
  display: 'block',
  color: theme.palette.mode === 'light' ? '#121212' : '#fff'
}));

const MusicContent = styled('div', { name: 'MusicContent' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  lineHeight: 1.33
}));

function SongDetailMobile({
  item,
  user,
  blockProps,
  identity,
  handleAction,
  state
}: SongDetailViewProps) {
  const {
    ItemActionMenu,
    ItemDetailInteraction,
    i18n,
    assetUrl,
    getSetting,
    useLoggedIn,
    dispatch,
    jsxBackend
  } = useGlobal();
  const classes = useStyles();
  const isLoggedIn = useLoggedIn();
  const owner = useGetItem(item?.owner);
  const autoPlay = getSetting('music.music_song.auto_play');
  const contextAudio = new AudioContext();
  const album = useGetItem(item?.album);
  const genres = useGetItems(item?.genres);

  if (!item) return null;

  const {
    creation_date,
    description,
    text,
    name,
    statistic,
    destination,
    is_featured,
    is_sponsor,
    image,
    attachments
  } = item;

  const PendingCard = jsxBackend.get('core.itemView.pendingReviewCard');

  const onEnded = () => {
    if (!isLoggedIn) return;

    dispatch({ type: 'music/updateTotalPlayItem', payload: { identity } });
  };

  const imageSong = getImageSrc(image, '500', assetUrl('music.song_no_image'));

  return (
    <Block blockProps={blockProps} testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <div className={classes.root}>
          <div className={classes.bgCoverWrapper}>
            <div className={classes.bgCover}></div>
            <div className={classes.header}>
              <div className={classes.headerInner}>
                <div className={classes.titleWrapper}>
                  <div className={classes.itemFlag}>
                    <FeaturedFlag variant="itemView" value={is_featured} />
                    <SponsorFlag
                      variant="itemView"
                      value={is_sponsor}
                      item={item}
                    />
                  </div>
                  <Box className={classes.pageTitle}>{name}</Box>
                </div>
                {Number.isInteger(statistic?.music_song_total_play) ? (
                  <div className={classes.minorInfo}>
                    {i18n.formatMessage(
                      { id: 'music_song_total_play' },
                      { value: statistic?.music_song_total_play }
                    )}
                  </div>
                ) : null}
                {album && (
                  <Box
                    sx={{ display: 'flex', justifyContent: 'center', mx: 2 }}
                    component="div"
                    mt={1}
                    fontWeight={600}
                  >
                    <span className={classes.albumLabel}>
                      {`${i18n.formatMessage({
                        id: 'album'
                      })}:`}
                    </span>
                    <Link to={album?.link || album?.url} ml={0.5}>
                      {album?.name}
                    </Link>
                  </Box>
                )}
              </div>
              <Image
                src={imageSong}
                className={classes.imgSong}
                aspectRatio={'11'}
              />
              <AudioPlayer
                autoPlay={autoPlay && contextAudio.state === 'running'}
                className={classes.audioPlayer}
                src={destination}
                showJumpControls={false}
                showSkipControls
                customVolumeControls={[]}
                onEnded={onEnded}
                customControlsSection={[
                  <LineIcon
                    key="shuffle"
                    icon="ico-shuffle"
                    className={`${classes.btnControlAudio} ${classes.btnDisable}`}
                  />,
                  RHAP_UI.MAIN_CONTROLS,
                  RHAP_UI.ADDITIONAL_CONTROLS
                ]}
                customIcons={{
                  play: <LineIcon icon="ico-play" />,
                  pause: <LineIcon icon="ico-pause" />,
                  previous: (
                    <LineIcon
                      icon="ico-play-prev"
                      className={`${classes.btnControlAudio} ${classes.btnDisable}`}
                    />
                  ),
                  next: (
                    <LineIcon
                      icon="ico-play-next"
                      className={`${classes.btnControlAudio} ${classes.btnDisable}`}
                    />
                  ),
                  loop: (
                    <LineIcon
                      icon="ico-play-repeat"
                      className={classes.btnRepeatOn}
                    />
                  ),
                  loopOff: <LineIcon icon="ico-play-repeat" />
                }}
              />
            </div>
          </div>
          <PendingCard item={item} sx={{ borderRadius: 0 }} />
          <div className={classes.viewContainer}>
            <ItemActionMenu
              menuName="detailActionMenu"
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              state={state}
              handleAction={handleAction}
              className={classes.actionMenu}
            />
            <div className={classes.author}>
              <Box component="div">
                <UserAvatar user={user} size={48} />
              </Box>
              <div className={classes.authorInfo}>
                <UserName
                  color={'inherit'}
                  to={`/user/${user?.id}`}
                  user={user}
                  className={classes.userName}
                  hoverCard={false}
                />
                {owner?.resource_name !== user?.resource_name && (
                  <HeadlineSpan>
                    {i18n.formatMessage(
                      {
                        id: 'to_parent_user'
                      },
                      {
                        icon: () => <LineIcon icon="ico-caret-right" />,
                        parent_user: () => <OwnerStyled user={owner} />
                      }
                    )}
                  </HeadlineSpan>
                )}
                <DotSeparator sx={{ color: 'text.secondary', mt: 0.5 }}>
                  <FormatDate value={creation_date} format="LL" />
                  <PrivacyIcon
                    value={item?.privacy}
                    item={item?.privacy_detail}
                  />
                </DotSeparator>
              </div>
            </div>
            {description && (
              <MusicContent>
                <HtmlViewerWrapper>
                  <HtmlViewer html={text || description} />
                </HtmlViewerWrapper>
              </MusicContent>
            )}
            {genres?.length > 0 && (
              <Box mt={4} display="flex" flexWrap="wrap">
                {genres.map(genre => (
                  <TagItem key={genre?.id}>
                    <Link to={genre?.url}>{genre?.name}</Link>
                  </TagItem>
                ))}
              </Box>
            )}
            <AttachmentFile attachments={attachments} size="mini" />
            <ItemDetailInteraction
              identity={identity}
              handleAction={handleAction}
            />
          </div>
        </div>
      </BlockContent>
    </Block>
  );
}

export default SongDetailMobile;

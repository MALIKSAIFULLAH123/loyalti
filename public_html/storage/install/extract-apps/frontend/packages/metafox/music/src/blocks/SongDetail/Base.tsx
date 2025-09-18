import { Link, useGlobal, useGetItems, useGetItem } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent } from '@metafox/layout';
import { SongDetailViewProps } from '@metafox/music/types';
import {
  Image,
  FeaturedFlag,
  SponsorFlag,
  AuthorInfo,
  HtmlViewerWrapper,
  ItemTitle
} from '@metafox/ui';
import { Box, Typography, styled } from '@mui/material';
import * as React from 'react';
import Waveform from '../Waveform/Waveform';
import AttachmentFile from '@metafox/music/components/Attachment/attachment';
import { getImageSrc } from '@metafox/utils';
import { camelCase } from 'lodash';

const name = 'SongDetailView';

const MusicContent = styled('div', { name, slot: 'MusicContent' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(15),
    lineHeight: 1.33
  })
);

const TagItem = styled('div', {
  name,
  slot: 'tagItem',
  overridesResolver(props, styles) {
    return [styles.tagItem];
  }
})(({ theme }) => ({
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
  color: theme.palette.mode === 'light' ? '#121212' : '#fff',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));

const BgCoverWrapper = styled('div', { name, slot: 'BgCoverWrapper' })(
  ({ theme }) => ({
    height: 320,
    overflow: 'hidden',
    position: 'relative'
  })
);

const BgCoverInner = styled('div', { name, slot: 'BgCoverInner' })(
  ({ theme }) => ({
    position: 'absolute',
    left: 0,
    right: 0,
    top: 0,
    bottom: 0,
    zIndex: -1
  })
);

const BgCover = styled('div', { name, slot: 'BgCover' })(({ theme }) => ({
  height: '100%',
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center',
  backgroundSize: 'cover',
  filter: 'brightness(0.4) blur(50px)'
}));

const Header = styled('div', { name, slot: 'Header' })(({ theme }) => ({
  marginLeft: theme.spacing(2),
  marginRight: 'auto',
  color: '#fff',
  paddingTop: theme.spacing(4),
  display: 'flex',
  justifyContent: 'space-between'
}));

const HeaderInner = styled('div', { name, slot: 'HeaderInner' })(
  ({ theme }) => ({
    flex: 1,
    minWidth: 0,
    margin: theme.spacing(0, 3, 0, 2),
    display: 'flex',
    flexDirection: 'column'
  })
);

const ImgSong = styled(Image, { name, slot: 'ImgSong' })(({ theme }) => ({
  width: 212,
  height: 212
}));

const ViewContainer = styled('div', { name, slot: 'viewContainer' })(
  ({ theme }) => ({
    margin: theme.spacing(0, 2, 0, 2),
    borderRadius: theme.shape.borderRadius,
    backgroundColor: theme.mixins.backgroundColor('paper'),
    border: theme.mixins.border('secondary'),
    padding: theme.spacing(0, 2, 2, 2),
    position: 'relative',
    marginTop: -44
  })
);

const WrapActionStyled = styled(Box)(({ theme }) => ({
  position: 'absolute',
  right: 8,
  top: 8
}));

const TitleWrapper = styled('div', { name, slot: 'TitleWrapper' })(
  ({ theme }) => ({
    display: 'flex'
  })
);

const PageTitle = styled(Box, { name, slot: 'PageTitle' })(({ theme }) => ({
  margin: theme.spacing(1.5, 0),
  fontWeight: theme.typography.fontWeightSemiBold,
  minWidth: 0,
  fontSize: theme.spacing(3),
  lineHeight: 1.3,
  maxHeight: 72,
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  wordBreak: 'break-word',
  wordWrap: 'break-word',
  whiteSpace: 'normal',
  WebkitLineClamp: 1,
  display: '-webkit-box',
  WebkitBoxOrient: 'vertical',
  '& .ico-heart': {
    color: theme.palette.primary.main
  }
}));

const SongAlbum = styled(Box, { name, slot: 'SongAlbum' })(({ theme }) => ({
  display: 'flex',
  fontWeight: theme.typography.fontWeightSemiBold,
  marginTop: 1.5
}));

const MinorInfo = styled('div', { name, slot: 'MinorInfo' })(({ theme }) => ({
  marginTop: theme.spacing(1),
  color: theme.palette.text.hint,
  fontSize: 13,
  fontWeight: theme.typography.fontWeightBold
}));

const AlbumWrapper = styled(Box, { name, slot: 'AlbumWrapper' })(
  ({ theme }) => ({
    fontWeight: 'normal',
    color: theme.palette.text.hint
  })
);

const SoundWave = styled('div', { name, slot: 'SoundWave' })(({ theme }) => ({
  marginTop: 'auto'
}));

function SongDetail({
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
    jsxBackend,
    getSetting
  } = useGlobal();
  const genres = useGetItems(item?.genres);

  const autoPlay = getSetting('music.music_song.auto_play');
  const album = useGetItem(item?.album);

  if (!item) return null;

  const {
    description,
    text,
    name,
    statistic,
    destination,
    is_featured,
    is_sponsor,
    resource_name,
    image
    // is_favorite
  } = item;
  const PendingCard = jsxBackend.get('core.itemView.pendingReviewCard');

  const imageSong = getImageSrc(image, '500', assetUrl('music.song_no_image'));

  return (
    <Block testid={`detailview ${resource_name}`}>
      <BlockContent>
        <Box>
          <BgCoverWrapper>
            <BgCoverInner>
              <BgCover style={{ backgroundImage: `url(${imageSong})` }} />
            </BgCoverInner>
            <Header>
              <ImgSong
                data-testid="imgSong"
                src={imageSong}
                aspectRatio={'11'}
              />
              <HeaderInner data-testid="headerInner">
                <TitleWrapper>
                  <ItemTitle data-testid="itemTitle">
                    <FeaturedFlag variant="itemView" value={is_featured} />
                    <SponsorFlag
                      variant="itemView"
                      value={is_sponsor}
                      item={item}
                    />
                  </ItemTitle>
                </TitleWrapper>
                <PageTitle data-testid={camelCase('music song title')}>
                  {name}
                </PageTitle>
                <SongAlbum data-testid={camelCase('music song album')}>
                  {album && (
                    <>
                      <Box ml={0.5} mr={0.5}>
                        &bull;
                      </Box>
                      <AlbumWrapper mr={0.5}>
                        {i18n.formatMessage({ id: 'album' })}
                      </AlbumWrapper>
                      <Link to={album?.link || album?.url}>{album?.name}</Link>
                    </>
                  )}
                </SongAlbum>
                {Number.isInteger(statistic?.music_song_total_play) ? (
                  <MinorInfo data-testid={camelCase('music song total play')}>
                    {i18n.formatMessage(
                      { id: 'music_song_total_play' },
                      { value: statistic?.music_song_total_play }
                    )}
                  </MinorInfo>
                ) : null}
                <SoundWave data-testid={camelCase('music song soundwave')}>
                  {destination && (
                    <Waveform
                      autoPlay={autoPlay}
                      url={destination}
                      isPlaylist={false}
                      selectedSong={item}
                    />
                  )}
                </SoundWave>
              </HeaderInner>
            </Header>
          </BgCoverWrapper>

          <ViewContainer>
            <WrapActionStyled>
              <ItemActionMenu
                menuName="detailActionMenu"
                identity={identity}
                icon={'ico-dottedmore-vertical-o'}
                state={state}
                handleAction={handleAction}
                size="smaller"
              />
            </WrapActionStyled>
            <AuthorInfo item={item} statisticDisplay={false} />
            <Box mt={2}>
              <PendingCard item={item} />
            </Box>
            {description && (
              <MusicContent data-testid="descriptionSong">
                <HtmlViewerWrapper>
                  <HtmlViewer html={text || description} />
                </HtmlViewerWrapper>
              </MusicContent>
            )}
            {genres?.length > 0 && (
              <Box
                mt={4}
                display="flex"
                flexWrap="wrap"
                data-testid="genreSong"
              >
                {genres.map(genre => (
                  <TagItem
                    data-testid={`${genre?.name} genreSong`}
                    key={genre?.id}
                  >
                    {genre.is_active ? (
                      <Link to={genre?.url}>{genre?.name}</Link>
                    ) : (
                      <Typography variant="h6">{genre.name}</Typography>
                    )}
                  </TagItem>
                ))}
              </Box>
            )}
            <AttachmentFile attachments={item?.attachments} size="large" />
            <ItemDetailInteraction
              identity={identity}
              handleAction={handleAction}
            />
          </ViewContainer>
        </Box>
      </BlockContent>
    </Block>
  );
}

export default SongDetail;

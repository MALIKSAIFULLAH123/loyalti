import { useGetItems, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { SongDetailViewProps } from '@metafox/music/types';
import {
  Image,
  LineIcon,
  FeaturedFlag,
  SponsorFlag,
  HtmlViewerWrapper,
  AuthorInfo
} from '@metafox/ui';
import { Box, Skeleton, Typography, styled } from '@mui/material';
import * as React from 'react';
import AudioPlayer, { RHAP_UI } from 'react-h5-audio-player';
import 'react-h5-audio-player/lib/styles.css';
import PlayList from './PlayList';
import { getImageSrc } from '@metafox/utils';
import HtmlViewer from '@metafox/html-viewer';
import AttachmentFile from '@metafox/music/components/Attachment/attachment';
import { isArray, range } from 'lodash';

enum StatusRepeat {
  NoRepeat = 0,
  RepeatOne = 1,
  Repeat = 2
}

const name = 'PlaylistDetailMobile';

const MusicContent = styled(Box, { name })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  lineHeight: 1.33
}));

const Author = styled(Box, { name })(({ theme }) => ({
  display: 'flex'
}));

const BgCoverWrapper = styled(Box, { name })(({ theme }) => ({
  minHeight: '388px',
  overflow: 'hidden',
  position: 'relative'
}));

const BgCover = styled(Box, { name })(({ theme }) => ({
  height: '100%',
  backgroundImage: 'linear-gradient(to top, #aeacac, #121212)',
  position: 'absolute',
  left: 0,
  right: 0,
  top: 0,
  bottom: 0,
  zIndex: -2
}));

const Header = styled(Box, { name })(({ theme }) => ({
  width: '100%',
  marginLeft: 'auto',
  marginRight: 'auto',
  color: '#fff',
  paddingTop: theme.spacing(3),
  '& .rhap_container': {
    backgroundColor: 'unset',
    boxShadow: 'none'
  }
}));

const TitleWrapper = styled(Typography, { name })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center'
}));

const PageTitle = styled(Box, { name })(({ theme }) => ({
  fontWeight: theme.typography.fontWeightBold,
  flex: 1,
  minWidth: 0,
  fontSize: 18,
  lineHeight: 1.2,
  maxHeight: 72,
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  wordBreak: 'break-word',
  wordWrap: 'break-word',
  whiteSpace: 'normal',
  WebkitLineClamp: 1,
  display: '-webkit-box',
  WebkitBoxOrient: 'vertical',
  textAlign: 'center',
  padding: theme.spacing(0, 2)
}));

const PlaylistLabel = styled('span', { name })(({ theme }) => ({
  color: theme.palette.grey[500]
}));

const PlaylistName = styled('span', { name })(({ theme }) => ({
  color: '#fff',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  whiteSpace: 'nowrap',
  marginLeft: theme.spacing(0.5)
}));

const ImgSong = styled(Image, { name })(({ theme }) => ({
  width: 160,
  height: 160,
  margin: theme.spacing(3, 'auto')
}));

const ViewContainer = styled(Box, { name })(({ theme }) => ({
  width: '100%',
  maxWidth: 720,
  marginLeft: 'auto',
  marginRight: 'auto',
  backgroundColor: theme.mixins.backgroundColor('paper'),
  border: theme.mixins.border('secondary'),
  padding: theme.spacing(2),
  position: 'relative',
  '&>button': {
    width: 32,
    height: 32,
    position: 'absolute!important',
    top: theme.spacing(1),
    right: theme.spacing(1),
    '& .ico': {
      color: theme.palette.text.secondary,
      fontSize: 13
    }
  }
}));

const ItemContent = styled(Box, { name })(({ theme }) => ({
  fontSize: 15,
  lineHeight: 1.33,
  marginTop: theme.spacing(3),
  '& p + p': {
    marginBottom: theme.spacing(2.5)
  }
}));

const AudioPlayerStyled = styled(AudioPlayer, {
  name,
  slot: 'AudioPlayerStyled',
  shouldForwardProp: props => props !== 'disabledIcon'
})<{ disabledIcon?: boolean }>(({ theme, disabledIcon }) => ({
  margin: theme.spacing(0, 'auto', 3),
  display: 'block',
  backgroundColor: 'transparent',
  boxShadow: 'none',
  outline: 'none',
  '& .rhap_progress-section': {
    flexWrap: 'wrap'
  },
  '& .rhap_progress-container': {
    width: '100%',
    flex: 'none',
    margin: theme.spacing(0, 0, 2),
    outline: 'none',
    order: 1,
    '& .rhap_progress-bar-show-download': {
      backgroundColor: theme.mixins.backgroundColor('paper'),
      height: 2,
      '& .rhap_progress-filled': {
        backgroundColor: theme.palette.primary.main
      },
      '& .rhap_progress-indicator': {
        display: 'none'
      }
    }
  },
  '& .rhap_current-time': {
    order: 2,
    color: '#fff'
  },
  '& .rhap_total-time': {
    marginLeft: 'auto',
    color: '#fff',
    order: 3
  },
  '& .rhap_controls-section': {
    padding: theme.spacing(0, 2),
    alignItems: 'baseline',
    flex: 'none',
    '& .rhap_main-controls': {
      flex: 'none',
      '& button': {
        fontSize: 32,
        width: 32,
        height: 32,
        color: '#fff',
        outline: 'none',
        overflow: 'inherit',
        '&.rhap_play-pause-button': {
          margin: theme.spacing(1, 5, 0)
        }
      }
    },
    '& .rhap_additional-controls': {
      flex: 'none',
      '& button': {
        margin: theme.spacing(0, 0, 0, 'auto'),
        fontSize: 18,
        width: 18,
        height: 18,
        color: '#fff',
        outline: 'none'
      }
    }
  },
  ...(disabledIcon && {
    '& .ico': {
      color: `${theme.palette.grey[500]}!important`
    }
  })
}));

const BtnControlAudio = styled(LineIcon, {
  name,
  slot: 'BtnControlAudio',
  shouldForwardProp: props => props !== 'btnRepeatOn'
})<{ btnRepeatOn?: number }>(({ theme, btnRepeatOn }) => ({
  fontSize: 18,
  '&.ico-shuffle': {
    display: 'flex',
    alignItems: 'center',
    cursor: 'pointer'
  },
  ...(btnRepeatOn !== 0 && {
    color: theme.palette.primary.main
  })
}));

function PlaylistDetailMobile({
  item,
  user,
  blockProps,
  identity,
  handleAction,
  state,
  isAlbum
}: SongDetailViewProps) {
  const {
    ItemActionMenu,
    ItemDetailInteraction,
    i18n,
    assetUrl,
    jsxBackend,
    usePageParams,
    dispatch
  } = useGlobal();
  const [selectedSong, setSelectedSong] = React.useState();
  const pageParams = usePageParams();
  const refAudio = React.useRef();
  const audio = refAudio?.current?.audio?.current;

  const [orderPlay, setOrderPlay] = React.useState([]);
  const [shuffle, setShuffle] = React.useState<boolean>(false);
  const [shuffleLength, setShuffleLength] = React.useState<number>(-1);
  const [repeat, setRepeat] = React.useState(StatusRepeat.NoRepeat);

  const [loading, setLoading] = React.useState(false);

  const songs = useGetItems(item?.songs);

  React.useEffect(() => {
    setLoading(true);

    if (!item?.songs)
      dispatch({
        type: 'music/getListSong',
        meta: {
          onSuccess: ({ data }) => {
            setLoading(!data);
          }
        },
        payload: { identity }
      });
    else setLoading(false);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pageParams]);

  React.useEffect(() => {
    if (isArray(item?.songs)) {
      setOrderPlay(Array.from(Array(item?.songs?.length).keys()));
    }
  }, [item?.songs]);

  React.useEffect(() => {
    if (shuffle && repeat !== 2) setShuffleLength(songs.length - 1);
    else setShuffleLength(-1);

    // if (shuffle && repeatList) setShuffleLength(-1);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [shuffle, repeat]);

  if (!item) return null;

  const imagePlaylist = getImageSrc(
    item?.image,
    '500',
    assetUrl(`music.${isAlbum ? 'album' : 'playlist'}_no_image`)
  );

  const changeSong = (value: number, ended: boolean) => {
    const index = songs.findIndex(song => song?.id === selectedSong?.id);
    const indexShuffle = orderPlay.findIndex(order => index === order);

    if (
      (repeat !== 2 && index === songs.length - 1 && ended) ||
      (repeat !== 2 && shuffleLength === 0 && ended)
    ) {
      if (shuffle) setShuffleLength(songs.length - 1);

      return;
    }

    if (shuffle) {
      if (repeat !== 2) setShuffleLength(shuffleLength - 1);

      if (indexShuffle + value === orderPlay.length)
        setSelectedSong(songs[orderPlay[0]]);
      else setSelectedSong(songs[orderPlay[indexShuffle + value]]);
    } else {
      if (index + value === orderPlay.length) setSelectedSong(songs[0]);
      else setSelectedSong(songs[index + value]);
    }
  };

  const onEnded = e => {
    if (repeat === 1) {
      return;
    }

    changeSong(1, true);
  };

  const handleRepeat = () => {
    audio.loop = false;

    switch (repeat) {
      case 0:
        setRepeat(StatusRepeat.RepeatOne);
        break;
      case 1:
        setRepeat(StatusRepeat.Repeat);
        break;
      default:
        setRepeat(StatusRepeat.NoRepeat);
    }
  };

  const handleShuffle = () => {
    if (!selectedSong?.destination) return;

    setShuffle(!shuffle);
    setOrderPlay(array => {
      return array.sort((a, b) => 0.5 - Math.random());
    });
  };

  const EmptyPage = jsxBackend.get('core.block.no_content_with_icon');

  return (
    <Block blockProps={blockProps} testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <Box>
          <BgCoverWrapper>
            <BgCover />
            <Header>
              <Box>
                <TitleWrapper>
                  {item?.is_featured || item?.is_sponsor ? (
                    <Box sx={{ mb: 2 }}>
                      <FeaturedFlag
                        variant="itemView"
                        value={item?.is_featured}
                      />
                      <SponsorFlag
                        variant="itemView"
                        value={item?.is_sponsor}
                        item={item}
                      />
                    </Box>
                  ) : null}
                  <PageTitle component="h2">{selectedSong?.name}</PageTitle>
                </TitleWrapper>
                <Box
                  sx={{ display: 'flex', justifyContent: 'center', mx: 2 }}
                  component="div"
                  mt={1}
                  fontWeight={600}
                >
                  <PlaylistLabel>
                    {`${i18n.formatMessage({
                      id: isAlbum ? 'album' : 'playlist'
                    })}: `}
                  </PlaylistLabel>
                  <PlaylistName>{item.name}</PlaylistName>
                </Box>
              </Box>
              <ImgSong src={imagePlaylist} aspectRatio={'11'} />
              <AudioPlayerStyled
                ref={refAudio}
                disabledIcon={!selectedSong?.destination}
                src={selectedSong?.destination}
                showJumpControls={false}
                onEnded={onEnded}
                showSkipControls
                customVolumeControls={[]}
                customControlsSection={[
                  <BtnControlAudio
                    onClick={handleShuffle}
                    sx={shuffle && { color: 'primary.main' }}
                    key="icon"
                    icon="ico-shuffle"
                    btnRepeatOn={0}
                  />,
                  RHAP_UI.MAIN_CONTROLS,
                  <BtnControlAudio
                    onClick={handleRepeat}
                    sx={repeat !== 0 && { color: 'primary.main' }}
                    key="icon"
                    icon={
                      repeat === 1 ? 'ico-play-repeat-one-o' : 'ico-play-repeat'
                    }
                    btnRepeatOn={repeat}
                  />
                ]}
                onClickNext={e => changeSong(1, false)}
                onClickPrevious={e => changeSong(-1, false)}
                customIcons={{
                  play: <LineIcon icon="ico-play" />,
                  pause: <LineIcon icon="ico-pause" />,
                  previous: (
                    <BtnControlAudio icon="ico-play-prev" btnRepeatOn={0} />
                  ),
                  next: <BtnControlAudio icon="ico-play-next" btnRepeatOn={0} />
                  
                }}
              />
            </Header>
          </BgCoverWrapper>
          <ViewContainer>
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              state={state}
              handleAction={handleAction}
            />
            <Author>
              <AuthorInfo item={item} sx={{ mt: 0 }} />
            </Author>
            {item?.description && (
              <MusicContent>
                <HtmlViewerWrapper>
                  <HtmlViewer html={item?.text || item?.description} />
                </HtmlViewerWrapper>
              </MusicContent>
            )}
            {loading ? (
              <ItemContent>
                {range(1, 4).map(index => (
                  <Box
                    key={index.toString()}
                    sx={{
                      display: 'flex',
                      mt: 1,
                      justifyContent: 'space-between'
                    }}
                  >
                    <Skeleton sx={{ flex: 1 }} />
                    <Skeleton width="30px" sx={{ ml: 2 }} />
                  </Box>
                ))}
              </ItemContent>
            ) : (
              <ItemContent>
                {!item?.songs?.length ? (
                  <EmptyPage
                    image="ico-music-note-o"
                    title="no_songs_found"
                    {...(item.extra.can_edit && {
                      labelButton: isAlbum
                        ? 'add_new_song'
                        : 'find_your_favorite'
                    })}
                    action={
                      isAlbum
                        ? 'music/redirectToEditAlbum'
                        : 'music/redirectToAllSongs'
                    }
                    isIconButton={false}
                    description="find_your_favorite_music_and_add_to_your_list"
                  />
                ) : (
                  <PlayList
                    identity={identity}
                    item={item}
                    isAlbum={isAlbum}
                    songs={songs}
                    selectedSong={selectedSong}
                    setSelectedSong={setSelectedSong}
                  />
                )}
              </ItemContent>
            )}
            <AttachmentFile attachments={item?.attachments} size="mini" />
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

export default PlaylistDetailMobile;

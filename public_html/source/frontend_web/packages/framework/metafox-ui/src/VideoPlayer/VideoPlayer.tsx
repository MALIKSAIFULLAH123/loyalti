import { Link, useGlobal, useScript, useLocation } from '@metafox/framework';
import { Image, ImageMatureState, LineIcon } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { styled, Box, Typography } from '@mui/material';
import React, { useEffect } from 'react';
import loadable from '@loadable/component';
import { useInView } from 'react-intersection-observer';
import { uniqueId, isNaN } from 'lodash';
import Control from './Control';
import { formatTime } from './format';
import LoadingComponent from './LoadingComponent';
import { detect } from 'detect-browser';
import { alpha } from '@mui/system/colorManipulator';

const ReactPlayer = loadable(
  () =>
    import(
      /* webpackChunkName: "VideoPlayer" */
      'react-player'
    )
);
const name = 'VideoPlayer';

const ItemVideoPlayer = styled('div', { name, slot: 'root' })(({ theme }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  alignItems: 'center',
  overflow: 'hidden',
  position: 'relative',
  '& .react-player__preview': {
    position: 'relative',
    backgroundSize: 'contain !important',
    backgroundRepeat: 'no-repeat',
    '&:before': {
      content: '""',
      position: 'absolute',
      top: 0,
      bottom: 0,
      left: 0,
      right: 0,
      backgroundColor: '#000',
      opacity: 0.4
    }
  },
  '& .ico-play-circle-o': {
    fontSize: theme.mixins.pxToRem(72),
    color: '#fff',
    position: 'relative'
  }
}));

const PlayerContainer = styled(Box, {
  name,
  slot: 'playerContainer',
  shouldForwardProp(prop: string) {
    return !/isTiktok|isRumble|isModalPlayer|ratioPercent/i.test(prop);
  }
})<{ isTiktok: boolean; isModalPlayer?: boolean; isRumble?: boolean }>(
  ({ theme, isTiktok, isModalPlayer, isRumble, ratioPercent }) => ({
    width: '100%',
    height: '100%',
    '&:before': {
      content: '""',
      paddingTop: ratioPercent ?? '56.25%',
      backgroundColor: theme.palette.grey['A700'],
      display: 'block',
      width: '100%'
    },
    ...(isTiktok && {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      background:
        theme.palette.mode === 'dark'
          ? theme.palette.grey[600]
          : theme.palette.grey[100],
      ...(isModalPlayer && {
        backgroundColor: theme.palette.grey['A700'],
        color: 'white',
        overflowY: 'auto',
        maxHeight: '100%',
        '& a': {
          color: 'white !important'
        }
      }),
      '&:before': {
        display: 'none'
      }
    }),
    ...(isRumble && {
      '&:before': {
        display: 'none'
      }
    }),
    '& .fb-video': {
      display: 'flex',
      alignItems: 'center'
    }
  })
);

const PlayerWrapper = styled(Box, {
  name,
  slot: 'playerWrapper',
  shouldForwardProp: props => props !== 'isNativeControl'
})<{ isNativeControl?: boolean }>(({ theme, isNativeControl }) => ({
  width: '100%',
  height: '100%',
  ...(!isNativeControl && {
    position: 'absolute',
    top: 0,
    left: 0,
    zIndex: 0
  })
}));

const ThumbImageWrapper = styled('div', { name, slot: 'ThumbImageWrapper' })(
  ({ theme }) => ({
    width: '100%',
    height: '100%',
    position: 'absolute',
    top: 0,
    zIndex: 1,
    filter: 'brightness(0.7)'
  })
);

const ReactPlayerStyled = styled(ReactPlayer, {
  name,
  slot: 'reactPlayer',
  shouldForwardProp: props => props !== 'isModalPlayer'
})(({ theme }) => ({
  position: 'absolute',
  top: 0,
  left: 0,
  zIndex: 0,
  '.controls': {
    width: '10px'
  }
}));

const VideoEmbedStyled = styled(Box, {
  name,
  slot: 'videoEmbed',
  shouldForwardProp: props =>
    props !== 'isRumble' &&
    props !== 'ratio' &&
    props !== 'isTiktok' &&
    props !== 'isMobile' &&
    props !== 'isModalPlayer'
})<{
  isRumble?: boolean;
  ratio?: any;
  isTiktok?: boolean;
  isMobile?: boolean;
  isModalPlayer?: boolean;
}>(({ theme, isRumble, ratio, isTiktok, isMobile, isModalPlayer }) => ({
  ...(isTiktok && {
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'column',
    '& blockquote': {
      ...(!isMobile && {
        height: '100% !important',
        marginTop: 0,
        marginBottom: 0
      }),
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center'
    },
    '& iframe': {
      ...(isModalPlayer &&
        !isMobile && {
          height: '100% !important'
        })
    }
  }),
  ...(isRumble && {
    paddingTop: ratio ? `${ratio * 100}%` : '100%',
    '& iframe': {
      position: 'absolute',
      top: 0,
      width: '100%',
      height: '100%'
    }
  })
}));
const CustomPlayButton = styled(LineIcon, {
  name,
  slot: 'CustomPlayButton'
})({
  position: 'absolute !important',
  top: 0,
  bottom: 0,
  left: 0,
  right: 0,
  margin: 'auto',
  width: 'fit-content',
  height: 'fit-content',
  zIndex: 2,
  pointerEvents: 'none'
});

const ImageStyled = styled(Image, { name, slot: 'imageThumbnail' })(
  ({ theme }) => ({
    width: '100%',
    height: '100%'
  })
);

let count = 0;
const PROGRESS_INTERVAL = 200;
let watchedSeconds = 0;
const parsePlayedFractionSeek = x => Math.min(x / 100, 0.99);

export interface VideoPlayerProps {
  src: string;
  thumb_url?: any;
  modalUrl?: string;
  autoPlay?: boolean;
  autoplayIntersection?: boolean;
  idPlaying?: string;
  embed_code?: string;
  isModalPlayer?: boolean;
  width?: number;
  height?: number;
  isNativeControl?: boolean;
  isThumbnail?: boolean;
  detailLink?: string;
  ratioPercent?: string;
  isCountVideo?: boolean;
  id?: string | number;
  identity?: string;
  link?: string;
  linkParams?: Record<string, any>;
  matureProps?: Record<string, any>;
}

const MatureImageItem = styled(Box, {
  name,
  slot: 'MatureImageItem',
  overridesResolver(props, styles) {
    return [styles.MatureImageItem];
  }
})<{ isStrict?: boolean }>(({ theme, isStrict }) => ({
  position: 'absolute',
  containerName: 'imageMature',
  containerType: 'inline-size',
  left: 0,
  right: 0,
  bottom: 0,
  top: 0,
  padding: theme.spacing(2),
  textAlign: 'center',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  width: '100%',
  ...(isStrict && {
    background: `radial-gradient(circle, ${alpha(
      theme.palette.grey[700],
      0.7
    )} 0%, ${alpha(theme.palette.grey[500], 0.5)} 100%)`
  })
}));

const DescriptionMature = styled(Typography, {
  name,
  slot: 'DescriptionMature',
  overridesResolver(props, styles) {
    return [styles.DescriptionMature];
  }
})(({ theme }) => ({
  userSelect: 'none',
  marginTop: theme.spacing(0.5),
  [theme.breakpoints.up('sm')]: {
    padding: theme.spacing(0, 5)
  },
  '@container imageMature (max-width: 250px)': {
    // hide description when image too small
    display: 'none'
  }
}));

const ImageWrapperMature = styled(Box, {
  name,
  slot: 'ImageWrapperMature',
  shouldForwardProp: props => props !== 'canClick'
})<{ canClick?: boolean }>(({ theme, canClick }) => ({
  display: 'flex',
  position: 'absolute',
  left: 0,
  right: 0,
  top: 0,
  bottom: 0,
  alignItems: 'center',
  justifyContent: 'center',
  cursor: canClick ? 'pointer' : 'auto',
  overflow: 'hidden'
}));

const ImageBlockStyled = styled('img', {
  name,
  slot: 'ImageBlockStyled',
  shouldForwardProp: props => props !== 'isStrictMature'
})<{ isStrictMature?: boolean }>(({ theme, isStrictMature }) => ({
  filter: isStrictMature ? 'blur(5px)' : 'blur(25px)',
  objectFit: 'cover',
  width: '100%',
  height: '100%',
  borderRadius: 0
}));

function stripScripts(s) {
  const div = document.createElement('div');
  div.innerHTML = s;
  const scripts = div.getElementsByTagName('script');
  let i = scripts.length;
  while (i--) {
    scripts[i].parentNode.removeChild(scripts[i]);
  }

  return div.innerHTML;
}

const youtube_parser = url => {
  if (!url) return false;

  const regExp =
    /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/|shorts\/)|(watch\?))\??v?=?([^#&?]*).*/;
  const match = url.match(regExp);

  return match && match[7].length === 11 ? match[7] : false;
};

const tiktok_url_test = url => {
  return /https?:\/\/(?:m|www|vm)\.tiktok\.com\//.test(url);
};

const rumble_url_test = url => {
  return /https?:\/.rumble\.com\//.test(url);
};

const vimeo_url_test = url => {
  return /https?:\/.vimeo\.com\//.test(url);
};

const VideoEmbed = props => {
  const { useIsMobile } = useGlobal();
  const isMobile = useIsMobile(true);

  const { isModalPlayer, isTiktok, isRumble, ratio } = props || {};

  if (isTiktok) {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    useScript('https://www.tiktok.com/embed.js', isModalPlayer);
  }

  if (!props?.embed_code) return null;

  return (
    <VideoEmbedStyled
      isRumble={isRumble}
      isTiktok={isTiktok}
      ratio={ratio}
      isMobile={isMobile}
      isModalPlayer={isModalPlayer}
      dangerouslySetInnerHTML={{ __html: stripScripts(props?.embed_code) }}
    />
  );
};

const VideoPlayerParser = React.forwardRef((props: any, ref) => {
  const { url, embed_code } = props;
  const isTiktok = tiktok_url_test(url);
  const isRumble = rumble_url_test(url);

  if (embed_code && (isTiktok || isRumble)) {
    return (
      <VideoEmbed
        isTiktok={isTiktok}
        isRumble={isRumble}
        {...props}
        ref={ref}
      />
    );
  }

  return <ReactPlayerStyled {...props} ref={ref} />;
});
type MuteType = {
  volume?: number;
  muted?: boolean;
};

const RESOURCE_VIDEO = 'video';
const RESOURCE_PHOTO = 'photo';

const cachedTime = {};
let volumeGeneral: MuteType = {};

export default function VideoPlayer(props: VideoPlayerProps) {
  const {
    isThumbnail = false,
    src,
    thumb_url,
    modalUrl,
    autoPlay = false,
    autoplayIntersection = false,
    idPlaying: idPlayingProp,
    embed_code,
    isModalPlayer = false,
    width,
    height,
    isNativeControl: isNativeControlProps = false,
    detailLink = false,
    ratioPercent,
    id,
    isCountVideo,
    identity,
    linkParams: linkParamsProps = {},
    matureProps = {}
  } = props || {};
  const maturePropsSetting = {
    color: 'white',
    showDescription: true,
    ...matureProps
  };

  const {
    getAcl,
    assetUrl,
    useMediaPlaying,
    useSession,
    useIsMobile,
    dispatch,
    useGetItem,
    navigate,
    i18n,
    dialogBackend,
    getSetting
  } = useGlobal();
  const isMobile = useIsMobile();
  const { name: browserName } = detect() || {};
  const iOS = ['ios', 'crios'].includes(browserName);
  const counted = React.useRef(false);
  const minimumTimeCount = getSetting<number>(
    'video.minimal_play_time_threshold'
  );

  const youtubeIdVideo = youtube_parser(src);
  const isTiktok = tiktok_url_test(src);
  const isRumble = rumble_url_test(src);
  const isVimeo = vimeo_url_test(src);
  const isNativeControl =
    (embed_code && (isTiktok || isRumble || isVimeo)) ||
    youtubeIdVideo ||
    isNativeControlProps;

  const ratio = isNaN(height / width) ? 1 : height / width;
  const { user: authUser } = useSession();
  const idPlaying: any = React.useMemo(
    () => idPlayingProp || uniqueId('video'),
    [idPlayingProp]
  );
  const [playing, setPlaying] = useMediaPlaying(idPlaying);

  const [isReady, setIsReady] = React.useState(false);
  const [isEditControl, setIsEditControl] = React.useState(false);
  const [refScrollInView, inView] = useInView({
    threshold: 0.5
  });

  const videoPlayerRef = React.useRef(null);
  const controlRef = React.useRef(null);
  const videoContainerRef = React.useRef(null);

  const [videoState, setVideoState] = React.useState({
    muted: volumeGeneral?.muted ?? true,
    volume: volumeGeneral?.volume ?? 1,
    playbackRate: 1.0,
    played: 0,
    seeking: false,
    buffer: true,
    loadedSeconds: 0
  });
  const freezeRef = React.useRef(false);
  const location = useLocation();

  const item = useGetItem(identity);
  const { mature: matureDefault, _mature, extra, mature_config } = item || {};
  const mature = extra?.can_view_mature ? 0 : _mature ?? matureDefault ?? 0;

  React.useEffect(() => {
    setVideoState(prev => ({ ...prev, ...volumeGeneral }));
  }, [playing]);
  React.useEffect(() => {
    if (cachedTime[src]) {
      videoPlayerRef.current?.seekTo(cachedTime[src], 'fraction');
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [location?.pathname]);

  React.useEffect(() => {
    return () => {
      cachedTime[src] = undefined;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const {
    muted,
    volume,
    playbackRate,
    played,
    buffer,
    loadedSeconds,
    seeking
  } = videoState || {};

  React.useEffect(() => {
    cachedTime[src] = played;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [played]);

  const durationVideo = videoPlayerRef.current?.getDuration();

  const currentTime = videoPlayerRef.current
    ? videoPlayerRef.current?.getCurrentTime()
    : 0;
  const duration = videoPlayerRef.current ? durationVideo : 0;

  const thumbUrl = getImageSrc(thumb_url, '500', assetUrl('video.no_image'));

  const settingAutoplayIntersection =
    authUser?.video_settings?.user_auto_play_videos;
  const isAutoPlayIntersection =
    settingAutoplayIntersection && autoplayIntersection;

  const handleReady = e => {
    if (isReady) return;

    setIsReady(true);

    if (isAutoPlayIntersection) return;

    if (autoPlay && !playing) {
      setPlaying(true);
    }
  };

  const playVideo = React.useCallback(
    evt => {
      setPlaying(true);
    },
    [setPlaying]
  );

  const pauseVideo = React.useCallback(
    evt => {
      setPlaying(false);
    },
    [setPlaying]
  );

  useEffect(() => {
    if (isAutoPlayIntersection) {
      setPlaying(inView);
    }

    if (inView) {
      const isVideoEmbed = embed_code && (isTiktok || isRumble);

      if (isVideoEmbed && isCountVideo && id && !counted.current) {
        counted.current = true;
        dispatch({
          type: 'video/countViewVideo',
          payload: { id }
        });
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [inView]);

  // extra param support videoId in playlist, may be remove on future if react-player support auto get id video on playlist
  const configParamsExtra = youtubeIdVideo
    ? {
        youtube: {
          embedOptions: { videoId: youtubeIdVideo }
        }
      }
    : {};

  const loaded = loadedSeconds / durationVideo;

  const formatCurrentTime = formatTime(currentTime);
  const formatDuration = formatTime(duration);

  const playPauseHandler = e => {
    e.stopPropagation();

    if (played === 1) {
      videoPlayerRef.current?.seekTo(0);

      if (videoPlayerRef.current?.getInternalPlayer())
        videoPlayerRef.current?.getInternalPlayer()?.play();

      return;
    }

    // plays and pause the video (toggling)
    setPlaying(!playing);
  };

  const progressHandler = state => {
    watchedSeconds += PROGRESS_INTERVAL;

    // check minimum played or played end
    const isCountableView =
      watchedSeconds / 1000 > minimumTimeCount ||
      (state?.played === 1 && durationVideo < minimumTimeCount);

    if (isCountableView && isCountVideo && id && !counted.current) {
      counted.current = true;
      dispatch({
        type: 'video/countViewVideo',
        payload: { id }
      });
    }

    if (isNativeControl) return;

    if (count > 3000) {
      hideControlPlayer();
    } else if (controlRef.current?.visible) {
      count += PROGRESS_INTERVAL;
    }

    if (!seeking) {
      setVideoState({
        ...videoState,
        ...state
      });
    }
  };

  const seekHandler = (e, value) => {
    const played = parsePlayedFractionSeek(value);
    setVideoState({ ...videoState, played });
    videoPlayerRef.current?.seekTo(played, 'fraction');
  };

  const seekMouseUpHandler = (e, value) => {
    if (freezeRef.current) {
      setPlaying(true);
      freezeRef.current = false;
    }

    const played = parsePlayedFractionSeek(value);
    setVideoState({ ...videoState, seeking: false });
    videoPlayerRef.current?.seekTo(played, 'fraction');
  };

  const onSeekMouseDownHandler = e => {
    if (playing) {
      freezeRef.current = true;
    }

    setVideoState({ ...videoState, seeking: true });
  };

  const volumeChangeHandler = (e, value) => {
    e.stopPropagation();
    const newValues = {
      muted: Number(value) === 0 ? true : false,
      volume: value
    };

    volumeGeneral = newValues;
    setVideoState(prev => ({ ...prev, ...newValues }));
  };

  const muteHandler = e => {
    e.stopPropagation();
    const newValues = {
      muted: !videoState.muted,
      volume:
        videoState.volume === 0 && videoState.muted ? 1 : videoState.volume
    };

    volumeGeneral = newValues;
    setVideoState(prev => ({ ...prev, ...newValues }));
  };

  const onEnded = () => {
    showControlPlayer();
    watchedSeconds = 0;
    counted.current = false;
  };

  const bufferStartHandler = e => {
    setVideoState({ ...videoState, buffer: true });
  };

  const bufferEndHandler = e => {
    setVideoState({ ...videoState, buffer: false });
  };

  const onPlaybackRateChange = value => {
    if (videoState.playbackRate === value) return;

    setVideoState({ ...videoState, playbackRate: value });
  };

  const showControlPlayer = React.useCallback(() => {
    if (!controlRef.current) return;

    controlRef.current.setVisible(true);
    count = 0;
  }, []);

  const hideControlPlayer = () => {
    if (videoState.played === 1 || !playing || isEditControl) return;

    if (!controlRef.current) return;

    controlRef.current.setVisible(false);
  };

  const onFullScreen = () => {
    if (!controlRef.current || !videoContainerRef.current) return;

    if (isMobile) {
      const video = videoContainerRef.current?.querySelector('video');

      if (!video) return;

      if (video.webkitSupportsFullscreen) {
        video.webkitEnterFullscreen();

        return;
      }

      video.requestFullscreen();

      return;
    }

    if (!document.fullscreenElement) {
      if (videoContainerRef.current?.requestFullscreen) {
        videoContainerRef.current?.requestFullscreen();
      } else if (videoContainerRef.current?.webkitRequestFullscreen) {
        /* Safari */
        videoContainerRef.current?.webkitRequestFullscreen();
      }
    } else {
      document.exitFullscreen();
    }
  };

  const [showFullScreen, setShowFullScreen] = React.useState(false);

  React.useEffect(() => {
    const handleFullScreen = () => {
      if (document.fullscreenElement) {
        setShowFullScreen(true);
      } else {
        setShowFullScreen(false);
      }
    };

    document.addEventListener('fullscreenchange', handleFullScreen);

    return () => {
      document.removeEventListener('fullscreenchange', handleFullScreen);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleConfirmWarningMature = React.useCallback(async e => {
    e.preventDefault();
    e.stopPropagation();
    const ok = await dialogBackend.confirm({
      message: i18n.formatMessage({
        id: mature_config?.message ?? 'video_mature_warning_desc'
      }),
      title: i18n.formatMessage({
        id: mature_config?.title ?? 'video_mature_warning_title'
      })
    });

    if (ok) {
      if (identity) {
        dispatch({
          type: 'editItemLocal',
          payload: { identity, data: { _mature: 0 } }
        });
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleClickStrictMature = React.useCallback(() => {
    const link = modalUrl || detailLink;

    if (!link) return null;

    navigate(link, {
      state: { asModal: true, ...linkParamsProps }
    });
  }, [navigate, modalUrl, detailLink, linkParamsProps]);

  if (mature) {
    const isStrictMature = mature === ImageMatureState.Strict;

    const resourceVideo =
      item?.resource_name === RESOURCE_VIDEO ? RESOURCE_VIDEO : RESOURCE_PHOTO;
    const ageMature =
      getAcl(`${resourceVideo}.${resourceVideo}.mature_video_age_limit`) ??
      getAcl('photo.photo.mature_photo_age_limit');
    const strictImageBg =
      assetUrl(`${resourceVideo}.strict_image`) ??
      assetUrl('photo.strict_image');

    const handleClickMature = isStrictMature
      ? handleClickStrictMature
      : handleConfirmWarningMature;

    let messageMature =
      mature_config?.short_message ||
      mature_config?.message ||
      'mature_photo_warning_message';

    if (isStrictMature) {
      messageMature = mature_config?.message || 'mature_photo_strict_message';
    }

    return (
      <PlayerContainer
        sx={{ position: 'relative' }}
        ratioPercent={ratioPercent}
      >
        <ImageWrapperMature
          canClick={!(isStrictMature && !detailLink)}
          onClick={handleClickMature}
        >
          <ImageBlockStyled
            alt="img-mature"
            isStrictMature={isStrictMature}
            src={isStrictMature ? strictImageBg : thumbUrl}
          />
          <MatureImageItem isStrict={isStrictMature} {...maturePropsSetting}>
            <LineIcon
              icon={isStrictMature ? 'ico-eye-off-o' : 'ico-warning-o'}
              sx={{ fontSize: '40px' }}
            />
            {maturePropsSetting?.showDescription ? (
              <DescriptionMature>
                {i18n.formatMessage(
                  {
                    id: messageMature
                  },
                  { age: ageMature }
                )}
              </DescriptionMature>
            ) : null}
          </MatureImageItem>
        </ImageWrapperMature>
      </PlayerContainer>
    );
  }

  if (!src) return null;

  if (isThumbnail)
    return (
      <ItemVideoPlayer>
        <PlayerContainer
          ratioPercent={ratioPercent}
          isTiktok={isTiktok}
          isRumble={isRumble}
        >
          <ThumbImageWrapper>
            {src && !thumb_url ? (
              <video
                src={iOS ? `${src}#t=0.1` : src}
                controls={false}
                style={{ width: '100%', height: '100%' }}
                muted
                playsInline
                preload={'metadata'}
              />
            ) : (
              <ImageStyled src={thumbUrl} aspectRatio={'fixed'} />
            )}
          </ThumbImageWrapper>
          <CustomPlayButton icon="ico-play-circle-o" />
        </PlayerContainer>
      </ItemVideoPlayer>
    );

  if (modalUrl)
    return (
      <ItemVideoPlayer>
        <PlayerContainer
          ratioPercent={ratioPercent}
          isTiktok={isTiktok}
          isRumble={isRumble}
        >
          <Link role="link" to={modalUrl} asModal>
            <ThumbImageWrapper>
              {src && !thumb_url ? (
                <video
                  src={iOS ? `${src}#t=0.1` : src}
                  controls={false}
                  style={{ width: '100%', height: '100%' }}
                  muted
                  playsInline
                  preload={'metadata'}
                />
              ) : (
                <ImageStyled src={thumbUrl} aspectRatio={'fixed'} />
              )}
            </ThumbImageWrapper>
          </Link>
          <CustomPlayButton icon="ico-play-circle-o" />
        </PlayerContainer>
      </ItemVideoPlayer>
    );

  return (
    <ItemVideoPlayer
      ref={refScrollInView}
      {...(!isNativeControl && {
        onMouseLeave: hideControlPlayer,
        onMouseMove: showControlPlayer
      })}
    >
      <PlayerContainer
        isTiktok={isTiktok}
        isRumble={isRumble}
        isModalPlayer={isModalPlayer}
        ref={videoContainerRef}
        ratioPercent={ratioPercent}
      >
        <PlayerWrapper
          {...(!isNativeControl && {
            onClick: playPauseHandler
          })}
          isNativeControl={isNativeControl}
        >
          {buffer && !isNativeControl && playing ? (
            <LoadingComponent size={50} />
          ) : null}
          <VideoPlayerParser
            ref={videoPlayerRef}
            onEnded={onEnded}
            ratio={ratio}
            onPause={pauseVideo}
            onPlay={playVideo}
            onClickPreview={playVideo}
            progressInterval={PROGRESS_INTERVAL}
            url={src}
            controls={!!isNativeControl}
            light={!autoPlay && !isAutoPlayIntersection ? thumbUrl : undefined}
            config={{
              ...configParamsExtra,
              ...(isMobile && {
                file: {
                  attributes: {
                    controlsList: 'nofullscreen',
                    disablePictureInPicture: '',
                    playsInline: true
                  }
                }
              })
            }}
            embed_code={embed_code}
            isModalPlayer={isModalPlayer}
            onReady={handleReady}
            className="player"
            width="100%"
            height="100%"
            playing={playing && !freezeRef.current}
            volume={volume}
            muted={muted}
            playbackRate={playbackRate}
            playIcon={<LineIcon icon="ico-play-circle-o" />}
            onProgress={progressHandler}
            {...(!isNativeControl && {
              onBuffer: bufferStartHandler,
              onBufferEnd: bufferEndHandler
            })}
          />
        </PlayerWrapper>
        {detailLink && playing && !embed_code ? (
          <Link
            role="link"
            to={detailLink}
            asModal
            sx={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}
          />
        ) : null}
        {!isNativeControl && isReady ? (
          <Control
            controlRef={controlRef}
            onPlayPause={playPauseHandler}
            playing={playing}
            played={played}
            loaded={loaded}
            buffer={buffer}
            onSeek={seekHandler}
            onSeekMouseUp={seekMouseUpHandler}
            onMouseSeekDown={onSeekMouseDownHandler}
            volume={muted ? 0 : volume}
            onVolumeChangeHandler={volumeChangeHandler}
            mute={muted}
            onMute={muteHandler}
            playRate={playbackRate}
            duration={duration}
            formatDuration={formatDuration}
            currentTime={formatCurrentTime}
            onPlaybackRateChange={onPlaybackRateChange}
            onFullScreen={onFullScreen}
            showFullScreen={showFullScreen}
            setIsEditControl={setIsEditControl}
          />
        ) : null}
      </PlayerContainer>
    </ItemVideoPlayer>
  );
}

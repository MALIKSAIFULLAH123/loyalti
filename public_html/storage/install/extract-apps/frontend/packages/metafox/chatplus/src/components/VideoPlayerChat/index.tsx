import { useGlobal, useLocation } from '@metafox/framework';
import { Image, LineIcon } from '@metafox/ui';
import { styled, Box } from '@mui/material';
import React, { useEffect } from 'react';
import loadable from '@loadable/component';
import { useInView } from 'react-intersection-observer';
import { uniqueId } from 'lodash';
import Control from './Control';
import { formatTime, getRatioPercent } from './format';
import LoadingComponent from './LoadingComponent';
import { getImageSrc } from '@metafox/utils';

const ReactPlayer = loadable(
  () =>
    import(
      /* webpackChunkName: "VideoPlayer" */
      'react-player'
    )
);
const name = 'VideoPlayer';

const ItemVideoPlayer = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  alignItems: 'center',
  overflow: 'hidden',
  position: 'relative',
  '& .react-player__preview': {
    position: 'relative',
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
    fontSize: theme.mixins.pxToRem(48),
    color: '#fff',
    position: 'relative'
  }
}));

const PlayerContainer = styled('div', {
  name,
  slot: 'playerContainer',
  shouldForwardProp(prop: string) {
    return !/ratioPercent/i.test(prop);
  }
})<{ ratioPercent?: any }>(({ theme, ratioPercent }) => ({
  width: '100%',
  height: '100%',
  ...(ratioPercent && {
    '&:before': {
      content: '""',
      paddingTop: ratioPercent ?? '56.25%',
      backgroundColor: theme.palette.grey['A700'],
      display: 'block',
      width: '100%',
      borderRadius: theme.shape.borderRadius
    }
  })
}));

const PlayerWrapper = styled(Box, {
  name,
  slot: 'playerWrapper'
})(({ theme }) => ({
  width: '100%',
  height: '100%'
}));

const ReactPlayerStyled = styled(ReactPlayer, {
  name,
  slot: 'reactPlayer'
})(({ theme }) => ({
  display: 'flex',
  minHeight: '104px',
  '.controls': {
    width: '10px'
  },
  '& video': {
    maxWidth: '100%',
    display: 'inline-block',
    borderRadius: theme.shape.borderRadius
  }
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
    height: '100%',
    '& img': {
      borderRadius: theme.shape.borderRadius
    }
  })
);

let count = 0;
const PROGRESS_INTERVAL = 200;
const parsePlayedFractionSeek = x => Math.min(x / 100, 0.99);

export interface VideoPlayerProps {
  src: string;
  autoPlay?: boolean;
  autoplayIntersection?: boolean;
  idPlaying?: string;
  width?: number;
  height?: number;
  ratioPercent?: string;
  id?: string | number;
  isMinimize?: boolean;
  currentTime?: string;
  thumb_url?: string;
  allowOpenPreview?: boolean;
  isDialog?: boolean;
}

const VideoPlayerParser = React.forwardRef((props: any, ref) => {
  return <ReactPlayerStyled {...props} ref={ref} />;
});
type MuteType = {
  volume?: number;
  muted?: boolean;
};

const cachedTime = {};
let volumeGeneral: MuteType = {};

export default function VideoPlayerChat(props: VideoPlayerProps) {
  const {
    src,
    autoPlay = false,
    autoplayIntersection = false,
    idPlaying: idPlayingProp,
    width,
    height,
    isMinimize = true,
    currentTime: currentTimeProps,
    thumb_url,
    allowOpenPreview,
    isDialog = false
  } = props || {};

  const {
    dialogBackend,
    useMediaPlaying,
    useSession,
    useIsMobile,
    assetUrl,
    useDialog
  } = useGlobal();
  const { dialogProps } = useDialog();
  const isMobile = useIsMobile();
  const counted = React.useRef(false);

  const { user: authUser } = useSession();
  const idPlaying: any = React.useMemo(
    () => idPlayingProp || uniqueId('video'),
    [idPlayingProp]
  );

  const ratioPercent = getRatioPercent(height, width);
  const thumbUrl = getImageSrc(thumb_url, '500', assetUrl('video.no_image'));

  const [playing, setPlaying] = useMediaPlaying(idPlaying);

  const [isReady, setIsReady] = React.useState(false);
  const [isEditControl, setIsEditControl] = React.useState(false);
  const [refScrollInView, inView] = useInView({
    threshold: 0.5
  });
  const [initState, setInitState] = React.useState(true);

  const videoPlayerRef = React.useRef(null);
  const controlRef = React.useRef(null);
  const videoContainerRef = React.useRef(null);

  const [videoState, setVideoState] = React.useState({
    muted: volumeGeneral?.muted ?? true,
    volume: volumeGeneral?.volume ?? 1,
    played: 0,
    seeking: false,
    buffer: true,
    loadedSeconds: 0
  });
  const freezeRef = React.useRef(false);
  const location = useLocation();

  React.useLayoutEffect(() => {
    if (!autoPlay) return;

    setInitState(true);
  }, [autoPlay]);

  React.useEffect(() => {
    setVideoState(prev => ({ ...prev, ...volumeGeneral }));
  }, [playing]);

  React.useEffect(() => {
    if (cachedTime[src] || currentTimeProps) {
      videoPlayerRef.current?.seekTo(
        cachedTime[src] || currentTimeProps,
        'fraction'
      );
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [location?.pathname, currentTimeProps]);

  React.useEffect(() => {
    return () => {
      cachedTime[src] = undefined;
      setPlaying(false);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const { muted, volume, played, buffer, loadedSeconds, seeking } =
    videoState || {};

  React.useEffect(() => {
    cachedTime[src] = played;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [played]);

  const durationVideo = videoPlayerRef.current?.getDuration();

  const currentTime = videoPlayerRef.current
    ? videoPlayerRef.current?.getCurrentTime()
    : 0;
  const duration = videoPlayerRef.current ? durationVideo : 0;

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

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [inView]);

  const loaded = loadedSeconds / durationVideo;

  const formatCurrentTime = formatTime(currentTime);
  const formatDuration = formatTime(duration);

  const playInitState = e => {
    if (!allowOpenPreview) return;

    e.stopPropagation();

    setInitState(false);

    // plays and pause the video (toggling)
    setPlaying(true);
  };

  useEffect(() => {
    if (isDialog) {
      setInitState(false);
      setPlaying(true);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isDialog]);

  const playPauseHandler = (e, control = false) => {
    e.stopPropagation();

    if (played === 1) {
      videoPlayerRef.current?.seekTo(0);

      if (videoPlayerRef.current?.getInternalPlayer())
        videoPlayerRef.current?.getInternalPlayer()?.play();

      return;
    }

    if (playing && !dialogProps.open && !control) {
      dialogBackend.present({
        component: 'chatplus.dialog.VideoPlayer',
        props: {
          src,
          currentTime: played,
          thumbUrl
        }
      });

      return;
    }

    // plays and pause the video (toggling)
    setPlaying(!playing);
  };

  const progressHandler = state => {
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
    counted.current = false;
  };

  const bufferStartHandler = e => {
    setVideoState({ ...videoState, buffer: true });
  };

  const bufferEndHandler = e => {
    setVideoState({ ...videoState, buffer: false });
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

  return (
    <>
      <Box display={initState && thumb_url ? 'inherit' : 'none !important'}>
        <ItemVideoPlayer width={`${width}px !important`}>
          <PlayerContainer ratioPercent={ratioPercent}>
            <ThumbImageWrapper onClick={playInitState}>
              <ImageStyled src={thumbUrl} aspectRatio={'fixed'} />
            </ThumbImageWrapper>
            <CustomPlayButton icon="ico-play-circle-o" />
          </PlayerContainer>
        </ItemVideoPlayer>
      </Box>
      <ItemVideoPlayer
        display={initState && thumb_url ? 'none !important' : 'inherit'}
        ref={refScrollInView}
        onMouseLeave={hideControlPlayer}
        onMouseMove={showControlPlayer}
      >
        <PlayerContainer ref={videoContainerRef}>
          <PlayerWrapper onClick={playPauseHandler}>
            {buffer && playing ? (
              <LoadingComponent size={isMinimize ? 26 : 50} />
            ) : null}
            <VideoPlayerParser
              ref={videoPlayerRef}
              onEnded={onEnded}
              onPause={pauseVideo}
              onPlay={playVideo}
              onClickPreview={playVideo}
              progressInterval={PROGRESS_INTERVAL}
              url={src}
              controls={false}
              config={{
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
              onReady={handleReady}
              className="player"
              width={width}
              height={height}
              playing={playing && !freezeRef.current}
              volume={volume}
              muted={muted}
              playIcon={<LineIcon icon="ico-play-circle-o" />}
              onProgress={progressHandler}
              onBuffer={bufferStartHandler}
              onBufferEnd={bufferEndHandler}
            />
          </PlayerWrapper>
          {isReady ? (
            <Control
              controlRef={controlRef}
              onPlayPause={e => playPauseHandler(e, true)}
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
              duration={duration}
              formatDuration={formatDuration}
              currentTime={formatCurrentTime}
              onFullScreen={onFullScreen}
              showFullScreen={showFullScreen}
              setIsEditControl={setIsEditControl}
              isMinimize={isMinimize}
            />
          ) : null}
        </PlayerContainer>
      </ItemVideoPlayer>
    </>
  );
}

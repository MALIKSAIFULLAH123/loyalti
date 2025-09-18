import { useGlobal } from '@metafox/framework';
import { SizeImgProp } from '@metafox/story/components';
import { useStory, useStoryViewContext } from '@metafox/story/hooks';
import { PauseStatus, TypeSizeLiveVideo } from '@metafox/story/types';
import { getImageSrc } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import React from 'react';
import loadable from '@loadable/component';
import { HtmlViewerWrapper, TruncateText } from '@metafox/ui';
import HtmlViewer from '@metafox/html-viewer';
import {
  DEFAULT_COLOR,
  DEFAULT_FONTSIZE,
  HEIGHT_RATIO_SIZE,
  MAX_VIDEO_DURATION,
  TIME_NEXT_STORY_DEFAULT,
  TYPE_LIVE_VIDEO
} from '@metafox/story/constants';
import { isEmpty, isFunction } from 'lodash';
import ViewerLabel from './ViewerLabel';
import { roundNumber } from '../utils';

const ReactPlayer = loadable(
  () =>
    import(
      /* webpackChunkName: "VideoPlayer" */
      'react-player'
    )
);

const name = 'ContentStoryBlock';

const RootStyled = styled(Box, {
  name,
  slot: 'RootStyled',
  shouldForwardProp: prop => prop !== 'isTypeLiveVideo'
})<{ isTypeLiveVideo?: boolean }>(({ theme, isTypeLiveVideo }) => ({
  ...(isTypeLiveVideo && {
    cursor: 'pointer',
    top: 0,
    left: 0,
    bottom: 0,
    right: 0,
    position: 'absolute',
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center'
  })
}));

const TextContent = styled('div', {
  name,
  slot: 'TextContent',
  shouldForwardProp: prop =>
    prop !== 'fontFamily' &&
    prop !== 'position' &&
    prop !== 'type' &&
    prop !== 'color'
})<{ fontFamily?: string; position?: boolean; type?: string; color?: string }>(
  ({ theme, fontFamily, position, type, color }) => ({
    zIndex: 1,
    fontWeight: theme.typography.fontWeightRegular,
    fontSize: theme.mixins.pxToRem(28),
    wordBreak: 'break-word',
    wordWrap: 'break-word',
    color: '#fff',
    '& a': {
      color: color || '#fff',
      '&:hover': {
        textDecoration: 'underline',
        cursor: 'pointer'
      }
    },
    ...(fontFamily && {
      fontFamily
    }),
    boxSizing: 'border-box',
    lineHeight: 'normal'
  })
);

const VideoPlayer = styled(ReactPlayer, {
  name,
  slot: 'VideoPlayer',
  shouldForwardProp: props => props !== 'isTypeLiveVideo'
})<{
  rotation?: string;
  isTypeLiveVideo?: boolean;
}>(({ theme, rotation }) => ({
  '& video': {
    width: '100%',
    height: '100%',
    transform: `rotate(${rotation}deg)`,
    transformOrigin: 'center'
  }
}));

const ImageUrl = styled('img', {
  name,
  slot: 'ImageUrl'
})(({ theme }) => ({
  width: '100%',
  height: '100%',
  objectFit: 'contain',
  position: 'relative',
  maxWidth: 'unset'
}));

const WrapperLabel = styled(Box, {
  name,
  slot: 'WrapperLabel'
})(({ theme }) => ({
  position: 'absolute',
  top: '8px',
  left: '8px',
  zIndex: '2',
  display: 'flex',
  alignItems: 'center'
}));

const WrapperInfoLiveVideo = styled(Box, {
  name,
  slot: 'WrapperInfoLiveVideo'
})(({ theme }) => ({
  position: 'absolute',
  bottom: 0,
  right: 0,
  zIndex: '2',
  padding: theme.spacing(1),
  width: '100%'
}));

const TextInProgress = styled(Box, {
  name,
  slot: 'TextInProgress'
})(({ theme }) => ({
  backgroundColor: 'rgba(0, 0, 0, 0.7)',
  position: 'absolute',
  width: '100%',
  height: '100%',
  top: 0,
  left: 0,
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  fontSize: theme.mixins.pxToRem(20),
  fontWeight: theme.typography.fontWeightSemiBold,
  color: '#fff'
}));

function checkCachedImage(src) {
  const image = new Image();
  image.src = src;

  return image?.complete;
}

function ContentStoryBlock(props) {
  const { assetUrl, useGetItem, navigate, jsxBackend, getSetting, i18n } =
    useGlobal();
  const { handleNext } = useStory();
  const [sizeLiveVideo, setSizeLiveVideo] = React.useState<TypeSizeLiveVideo>(
    TypeSizeLiveVideo.LANDSCAPE
  );

  const [sizeFilePreview, setSizeFilePreview] = React.useState<SizeImgProp>({
    width: '100%',
    height: '100%'
  });

  const MaxVideoDuration: number =
    getSetting('story.video_duration') || MAX_VIDEO_DURATION;

  const LiveLabel = jsxBackend.get('livestreaming.ui.labelLive');

  const {
    story,
    height,
    width,
    storyContent: storyContentProps,
    onLoadCallBack
  } = props;

  const storyContent = useStoryViewContext();
  const {
    pauseStatus,
    readyStateFile,
    fire,
    mutedStatus,
    durationVideo,
    progressVideoPlay
  } = storyContentProps || storyContent || {};

  const wrapperRef = React.useRef<any>();
  const videoRef = React.useRef<any>();
  const fileRef = React.useRef<any>();
  const playedSecondsLive = React.useRef<number>(0);

  const {
    type,
    video,
    extra_params,
    background: backgroundIdentity,
    duration: durationProps,
    in_process
  } = story || {};
  const duration = durationProps || TIME_NEXT_STORY_DEFAULT;
  const isTypeVideo =
    (type === 'video' && in_process) || type === 'photo' || type === 'text'
      ? false
      : true;
  const isTypeLiveVideo = type === TYPE_LIVE_VIDEO ? true : false;

  const [videoPlaying, setVideoPlaying] = React.useState(false);

  const {
    isBrowser,
    storyHeight,
    texts: listText,
    transform,
    size: sizeExtra,
    is_streaming,
    total_viewer,
    short_description,
    title
  } = extra_params || {};

  const typeBackgroundBrowser = type === 'text' && isBrowser;

  const { rotation, scale = 1, position } = transform || {};

  const background = useGetItem(backgroundIdentity);

  const image = background?.image || story?.image;

  const imageUrl = getImageSrc(image, '1024', assetUrl('story.no_image'));

  const key = isTypeVideo ? `${video}${story?.id}` : `${imageUrl}${story?.id}`;

  React.useEffect(() => {
    if (video || !imageUrl) return;

    if (checkCachedImage(imageUrl)) return;

    onMediaLoadStart();

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [imageUrl, video]);

  React.useEffect(() => {
    if (!readyStateFile) return;

    if (pauseStatus !== PauseStatus.No) {
      setVideoPlaying(false);

      return;
    }

    setVideoPlaying(true);
  }, [pauseStatus, readyStateFile]);

  const onMediaLoadStart = () => {
    fire({ type: 'setReadyStateFile', payload: false });
    setVideoPlaying(false);
  };

  const onMediaLoad = () => {
    let videoWidth =
      sizeExtra?.width || videoRef.current?.props?.width || '100%';
    let videoHeight =
      sizeExtra?.height || videoRef.current?.props?.height || '100%';

    if (!isTypeVideo) {
      videoWidth = sizeExtra?.width || fileRef.current?.naturalWidth || '100%';
      videoHeight =
        sizeExtra?.height || fileRef.current?.naturalHeight || '100%';

      fire({ type: 'setReadyStateFile', payload: true });
      isFunction(onLoadCallBack) && onLoadCallBack();
    }

    const size = {
      width: videoWidth,
      height: videoHeight
    };

    setSizeFilePreview(size);
  };

  const onReadyVideo = e => {
    if (readyStateFile) return;

    fire({ type: 'setReadyStateFile', payload: true });
    playedSecondsLive.current = e.playedSeconds;
    setVideoPlaying(true);
    isFunction(onLoadCallBack) && onLoadCallBack();
  };

  const ratio = height / HEIGHT_RATIO_SIZE;
  const ratioScreen = height / storyHeight;

  const widthFilePreview = React.useMemo(() => {
    if (isTypeLiveVideo) {
      return 'auto';
    }

    if (sizeFilePreview?.width && scale !== 0) {
      return roundNumber(sizeFilePreview.width * scale * ratioScreen, width);
    }

    return '100%';
  }, [ratioScreen, scale, sizeFilePreview?.width, isTypeLiveVideo, width]);

  const heightFilePreview = React.useMemo(() => {
    if (isTypeLiveVideo) {
      return 'auto';
    }

    if (sizeFilePreview?.height && scale !== 0) {
      return roundNumber(sizeFilePreview.height * scale * ratioScreen, height);
    }

    return '100%';
  }, [ratioScreen, scale, sizeFilePreview?.height, isTypeLiveVideo, height]);

  const positionItem = React.useMemo(() => {
    return isTypeLiveVideo
      ? {
          display: 'flex',
          justifyContent: 'center',
          alignItems: 'center',
          width: '80%',
          position: 'relative',
          ...(sizeLiveVideo === TypeSizeLiveVideo.LANDSCAPE
            ? {
                maxHeight: '60%'
              }
            : {
                height: '60%'
              })
        }
      : {
          top: position?.top || 0,
          left: position?.left || 0,
          position: 'absolute',
          ...(in_process && {
            top: 0,
            left: 0,
            width: '100%',
            height: '100%'
          })
        };
  }, [
    isTypeLiveVideo,
    sizeLiveVideo,
    position?.top,
    position?.left,
    in_process
  ]);

  const handleViewLiveVideo = () => {
    if (!isTypeLiveVideo || !story?.item_id) return;

    navigate(`/live-video/${story?.item_id}`);
  };

  const onLiveVideoSize = React.useCallback(() => {
    const elePlayer = videoRef.current?.player?.player?.player;

    if (!isEmpty(elePlayer) && isTypeLiveVideo) {
      if (elePlayer?.videoWidth < elePlayer?.videoHeight) {
        setSizeLiveVideo(TypeSizeLiveVideo.PORTRAIT);
      } else {
        setSizeLiveVideo(TypeSizeLiveVideo.LANDSCAPE);
      }
    }
  }, [isTypeLiveVideo]);

  const handleProgress = e => {
    onLiveVideoSize();

    if (e?.loaded < 0.15) return;

    onReadyVideo(e);

    const timeProgress = e.playedSeconds - playedSecondsLive.current;

    // reset VideoPlayer for live-video or video mux
    if (
      (isTypeLiveVideo && timeProgress >= duration) ||
      (e.playedSeconds >= duration && durationVideo > MaxVideoDuration)
    ) {
      videoRef.current.seekTo(0, 'seconds');
    }

    if (is_streaming) {
      if (timeProgress < 0 && e.playedSeconds === 0) {
        handleNext();
      }

      fire({
        type: 'setProgressVideoPlay',
        payload: parseFloat(parseFloat(timeProgress).toFixed(1))
      });

      return;
    }

    fire({
      type: 'setProgressVideoPlay',
      payload: e.playedSeconds
    });
  };

  const handleDuration = e => {
    if (isTypeLiveVideo || !isTypeVideo) return;

    fire({
      type: 'setDuration',
      payload: e > MaxVideoDuration ? MaxVideoDuration : e
    });
  };

  const bufferStartHandler = e => {
    if (!readyStateFile) return;

    fire({
      type: 'setBuffer',
      payload: true
    });
  };

  const bufferEndHandler = e => {
    if (!readyStateFile) return;

    fire({
      type: 'setBuffer',
      payload: false
    });
  };

  const showLiveVideo = React.useMemo(() => {
    if (!isTypeLiveVideo) return false;

    return readyStateFile && progressVideoPlay > 0;
  }, [isTypeLiveVideo, readyStateFile, progressVideoPlay]);

  if (imageUrl || video)
    return (
      <RootStyled
        isTypeLiveVideo={isTypeLiveVideo}
        onClick={handleViewLiveVideo}
      >
        <Box sx={{ ...positionItem }}>
          <Box
            ref={wrapperRef}
            sx={{
              height: '100%',
              position: 'relative'
            }}
          >
            {showLiveVideo && is_streaming && LiveLabel ? (
              <WrapperLabel>
                <LiveLabel mr={1} />
                <ViewerLabel total_viewer={total_viewer} />
              </WrapperLabel>
            ) : null}
            {in_process ? (
              <>
                <ImageUrl
                  key={key}
                  src={imageUrl}
                  ref={fileRef}
                  onLoad={onMediaLoad}
                />
                <TextInProgress>
                  {i18n.formatMessage({ id: 'creating_story' })}
                </TextInProgress>
              </>
            ) : isTypeVideo ? (
              <VideoPlayer
                wrapper={isTypeLiveVideo ? React.Fragment : 'div'}
                muted={mutedStatus}
                loop={false}
                autoPlay
                progressInterval={100}
                controls={false}
                key={key}
                url={video}
                ref={videoRef}
                playing={videoPlaying}
                onLoadedMetadata={onMediaLoad}
                onProgress={handleProgress}
                onDuration={handleDuration}
                rotation={rotation}
                width={widthFilePreview}
                height={heightFilePreview}
                isTypeLiveVideo={isTypeLiveVideo}
                onBuffer={bufferStartHandler}
                onBufferEnd={bufferEndHandler}
                config={{
                  file: {
                    attributes: {
                      controlsList: 'nofullscreen',
                      disablePictureInPicture: '',
                      playsInline: true,
                      onLoadStart: onMediaLoadStart,
                      style: {
                        maxHeight: '100%',
                        maxWidth: '100%'
                      }
                    }
                  }
                }}
              />
            ) : (
              <ImageUrl
                key={key}
                src={imageUrl}
                ref={fileRef}
                onLoad={onMediaLoad}
                className={readyStateFile ? '' : 'srOnly'}
                style={{
                  transform: `rotate(${rotation}deg)`,
                  transformOrigin: 'center',
                  width: widthFilePreview,
                  height: heightFilePreview
                }}
              />
            )}
            {showLiveVideo && (title || short_description) ? (
              <WrapperInfoLiveVideo>
                <TruncateText variant="body1" lines={1}>
                  {title}
                </TruncateText>
                <TruncateText variant="body2" lines={1}>
                  {short_description}
                </TruncateText>
              </WrapperInfoLiveVideo>
            ) : null}
          </Box>
        </Box>

        {readyStateFile && listText?.length ? (
          <>
            {listText
              .filter(x => x?.text)
              .map(textItem => (
                <Box
                  key={textItem.id}
                  sx={{
                    top: textItem?.transform?.position?.top || 0,
                    left: textItem?.transform?.position?.left || 0,
                    position: 'absolute',
                    width: textItem?.width || width,
                    ...(typeBackgroundBrowser && {
                      top: 0,
                      left: 0,
                      width: '100%',
                      height: '100%',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center'
                    }),
                    ...(in_process && {
                      opacity: 0.2
                    })
                  }}
                >
                  <TextContent
                    type={type}
                    fontFamily={textItem?.fontFamily}
                    color={textItem?.color ?? DEFAULT_COLOR}
                    style={{
                      transform: `rotate(${
                        textItem?.transform?.rotation
                      }deg) scale(${textItem?.transform?.scale || 1})`,
                      transformOrigin: 'center',
                      color: textItem?.color ?? DEFAULT_COLOR,
                      textAlign: textItem?.textAlign ?? 'center',
                      fontSize:
                        (textItem?.fontSize ?? DEFAULT_FONTSIZE) *
                        (typeBackgroundBrowser ? 1 : ratio)
                    }}
                  >
                    <HtmlViewerWrapper mt={0} sx={{ whiteSpace: 'normal' }}>
                      <HtmlViewer html={textItem?.text_parsed} />
                    </HtmlViewerWrapper>
                  </TextContent>
                </Box>
              ))}
          </>
        ) : null}
      </RootStyled>
    );

  return null;
}

export default ContentStoryBlock;

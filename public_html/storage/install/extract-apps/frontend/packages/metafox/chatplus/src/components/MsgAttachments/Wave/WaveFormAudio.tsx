/**
 * @type: ui
 * name: chatplus.ui.messageWaveForm
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, styled, LinearProgress } from '@mui/material';
import React from 'react';
import WaveShape from './WaveShape';
import {
  ProgressUpdateInterval,
  throttle,
  parseProgressToTimer
} from './utils';

const name = 'messageWaveForm';

const RootStyled = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  position: 'relative',
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(0, 1),
  paddingTop: theme.spacing(1),
  '& .ico': {
    fontSize: 18,
    cursor: 'pointer'
  }
}));

const ButtonControlWrapper = styled(Box, {
  name,
  slot: 'ButtonControlWrapper'
})(({ theme }) => ({
  marginRight: theme.spacing(1),
  position: 'relative'
}));

const InfoWapper = styled(Box, {
  name,
  slot: 'InfoWapper'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'center',
  alignItems: 'center',
  minWidth: 32,
  marginLeft: 'auto'
}));

const LineIconStyled = styled(LineIcon, {
  name,
  slot: 'LineIcon',
  shouldForwardProp: props => props !== 'disabled' && props !== 'isOwner'
})<{ disabled?: boolean; isOwner?: boolean }>(
  ({ theme, disabled, isOwner }) => ({
    color: isOwner ? '#fff !important' : '#000',
    ...(theme.palette.mode === 'dark' && {
      color: '#fff !important'
    }),
    ...(disabled && {
      color: `${theme.palette.grey[500]}!important`
    })
  })
);

const PlayBackRateStyled = styled(Box, {
  name,
  slot: 'playBackRate',
  shouldForwardProp: props => props !== 'disabled'
})<{ disabled?: boolean }>(({ theme, disabled }) => ({
  padding: theme.spacing(0.5, 1),
  backgroundColor:
    theme.palette.mode === 'dark'
      ? theme.palette.grey['400']
      : theme.palette.grey['300'],
  borderRadius: theme.shape.borderRadius,
  marginTop: theme.spacing(1),
  color:
    theme.palette.mode === 'dark'
      ? theme.palette.text.primary
      : theme.palette.text.secondary,
  WebkitUserSelect: 'none',
  userSelect: 'none'
}));

const SupportIosPlayer = styled(Box, {
  name,
  slot: 'SupportIosPlayer'
})(({ theme }) => ({
  position: 'absolute',
  left: 0,
  right: 0,
  bottom: 0,
  top: 0,
  zIndex: 2
}));

interface IProps {
  url: string;
  isOwner: boolean;
  audioDuration: any;
  isPageAllMessages: boolean;
}

function WaveFormAudio({
  url,
  isOwner,
  audioDuration,
  isPageAllMessages
}: IProps) {
  const { useMediaPlaying } = useGlobal();
  const idPlaying = isPageAllMessages
    ? `msg-audio-all-${url}`
    : `msg-audio-${url}`;
  const audioRef = React.useRef<HTMLAudioElement>();

  const [playing, setPlaying] = useMediaPlaying(idPlaying);

  const [isReady, setIsReady] = React.useState<boolean>(false);

  const [playBackRate, setPlayBackRate] = React.useState<number>(1);
  const [progress, setProgress] = React.useState(0);
  const isDraggingProgress = React.useRef(false);

  const setIsDraggingProgress = x => (isDraggingProgress.current = x);

  const duration = audioDuration || audioRef.current?.duration;
  const [shouldInit, setShouldInit] = React.useState(false);
  const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent);

  const [supportIosPlayerReady, setSupportIosPlayerReady] =
    React.useState(false);

  const handleReady = (play = false) => {
    setIsReady(true);

    if (play) {
      playAudioPromise();
    }
  };

  const handlePlayPause = () => {
    if (!isReady) {
      handleReady(true);

      return;
    }

    setPlaying(!playing);

    playAudioPromise();
  };

  const onAudioReady = () => {
    handleReady();
  };

  const handlePlayBackRate = () => {
    if (!isReady) return;

    const rate = audioRef.current.playbackRate;

    if (rate >= 2) {
      audioRef.current.playbackRate = 0.5;
      setPlayBackRate(0.5);

      return;
    }

    audioRef.current.playbackRate = rate + 0.5;
    setPlayBackRate(rate + 0.5);
  };

  const playAudioPromise = () => {
    if (!audioRef.current) return;

    if (audioRef.current.error) {
      audioRef.current.load();
    }

    const playPromise = audioRef.current.play();

    // playPromise is null in IE 11
    if (playPromise) {
      playPromise.then(null).catch(err => {
        audioRef.current.play();
      });
    } else {
      // Remove forceUpdate when stop supporting IE 11
      // this.forceUpdate();
    }
  };

  const onPlay = React.useCallback(
    evt => {
      if (playing) return;

      setPlaying(true);
    },
    [setPlaying, playing]
  );

  const onPause = React.useCallback(
    evt => {
      if (!playing) return;

      setPlaying(false);
    },
    [setPlaying, playing]
  );

  const onEnded = () => {
    setPlaying(false, true);
    setProgress(0);
  };

  const handleAudioTimeUpdate = throttle((e: Event): void => {
    const audio = e.target as HTMLAudioElement;

    if (!audio) return;

    const { currentTime } = audio;

    if (isDraggingProgress.current) return;

    const percent = parseFloat(
      ((currentTime / duration) * 100 || 0).toFixed(2)
    );

    setProgress(percent);

    if (!isDraggingProgress.current) {
      if (percent === 0 || percent === 100) {
        setProgress(0);
      }
    }
  }, ProgressUpdateInterval);

  React.useEffect(() => {
    if (!audioRef.current) return;

    audioRef.current.preservesPitch = true;

    audioRef.current.addEventListener('timeupdate', handleAudioTimeUpdate);
  }, [handleAudioTimeUpdate]);

  React.useEffect(() => {
    if (!audioRef.current || !isReady) return;

    const paused = audioRef.current?.paused;

    if (playing) {
      setSupportIosPlayerReady(true);
    }

    if (!playing && paused) {
      return;
    }

    playing ? playAudioPromise() : audioRef.current.pause();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [playing]);

  React.useEffect(() => {
    try {
      fetch(url, {
        mode: 'no-cors',
        headers: {
          'Access-Control-Allow-Origin': '*'
        }
      })
        .then(res => res.blob())
        .then(myBlob => {
          audioRef.current = new Audio(url);
          audioRef.current.addEventListener('loadeddata', onLoadedData);
          audioRef.current.addEventListener('loadedmetadata', onLoadedData);
        });
    } catch (error) {
      if (audioRef.current) return;

      audioRef.current = new Audio(url);
      audioRef.current.addEventListener('loadeddata', onLoadedData);
      audioRef.current.addEventListener('loadedmetadata', onLoadedData);
    }

    return () => {
      audioRef.current?.pause();
      setPlaying(false, true);
      audioRef.current?.removeEventListener('loadeddata', onLoadedData);
      audioRef.current?.removeEventListener('loadedmetadata', onLoadedData);
    };
  }, []);
  const onLoadedData = () => {
    if (shouldInit) return;

    setImmediate(() => {
      setShouldInit(true);
      onAudioReady();
      audioRef.current.addEventListener('play', onPlay);
      audioRef.current.addEventListener('pause', onPause);
      audioRef.current.addEventListener('ended', onEnded);
    });
  };

  const handleSupportIosPlayer = () => {
    // ios version > 17 need to play first before do anything
    handlePlayPause();
    setSupportIosPlayerReady(true);
  };

  return (
    <RootStyled>
      {!shouldInit ? (
        <Box
          sx={{
            width: '100%',
            position: 'absolute',
            left: 0,
            right: 0,
            bottom: 0,
            top: 0,
            zIndex: 3
          }}
        >
          <LinearProgress />
        </Box>
      ) : null}
      <ButtonControlWrapper onClick={handlePlayPause}>
        {!playing ? (
          <LineIconStyled icon="ico-play" isOwner={isOwner} />
        ) : (
          <LineIconStyled icon="ico-pause" isOwner={isOwner} />
        )}
      </ButtonControlWrapper>
      <Box position="relative" flex={1} minWidth={0}>
        <WaveShape
          isOwner={isOwner}
          progress={progress}
          audioRef={audioRef}
          duration={duration}
          setProgress={setProgress}
          isDraggingProgress={isDraggingProgress}
          setIsDraggingProgress={setIsDraggingProgress}
        />
        {isIos && !supportIosPlayerReady ? (
          <SupportIosPlayer onClick={handleSupportIosPlayer} />
        ) : null}
      </Box>
      <InfoWapper>
        <div>{parseProgressToTimer(progress, duration)}</div>
        {playing ? (
          <PlayBackRateStyled disabled={!isReady} onClick={handlePlayBackRate}>
            {playBackRate}x
          </PlayBackRateStyled>
        ) : null}
      </InfoWapper>
    </RootStyled>
  );
}

export default WaveFormAudio;

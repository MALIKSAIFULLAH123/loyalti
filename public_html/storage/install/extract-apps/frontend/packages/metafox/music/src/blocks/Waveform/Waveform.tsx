import { LineIcon } from '@metafox/ui';
import { LinearProgress, Slider, Theme, Box, Tooltip } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import React, { useEffect, useRef, useState } from 'react';
import WaveSurfer from 'wavesurfer.js';
import { useGlobal, getItemsSelector } from '@metafox/framework';
import { useSelector } from 'react-redux';
import clsx from 'clsx';
import { isEmpty, last } from 'lodash';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'flex',
        alignItems: 'flex-end',
        '& .ico': {
          color: '#fff',
          fontSize: 18,
          cursor: 'pointer'
        }
      },
      hover: {
        position: 'absolute',
        left: 0,
        top: 0,
        zIndex: 10,
        pointerEvents: 'none',
        height: '100%',
        width: 0,
        mixBlendMode: 'overlay',
        background: 'rgba(255, 255, 255, 0.3)',
        opacity: 0,
        transition: 'opacity 0.2s ease'
      },
      waveForm: {
        cursor: 'pointer',
        margin: theme.spacing(0, 1, 1),
        height: 77,
        overflow: 'hidden',
        flex: 1,
        position: 'relative',
        '&:hover $hover': {
          opacity: 1
        },
        '&::before': {
          content: "''",
          height: '2px',
          background: '#eee',
          width: '100%',
          position: 'absolute',
          bottom: 0
        },
        '& > wave': {
          overflowX: 'inherit !important',
          cursor: 'pointer !important'
        },
        '& wave > wave::before': {
          content: "''",
          position: 'absolute',
          top: '50%',
          height: '2px',
          width: '100%',
          background: '#2682d5'
        }
      },
      btnControlWrapper: {
        marginRight: theme.spacing(2)
      },
      btnControl: {
        border: 'none',
        background: 'transparent',
        outline: 'none',
        padding: 0,
        '& + $btnControl': {
          paddingLeft: theme.spacing(2)
        }
      },
      btnPlayPause: {},
      btnShuffle: {
        marginLeft: theme.spacing(2)
      },
      btnRepeat: {
        marginLeft: theme.spacing(2)
      },
      activeIcon: {
        color: `${theme.palette.primary.main} !important`
      },
      timePlaying: {
        minWidth: theme.spacing(5),
        textAlign: 'right',
        fontSize: 15
      },
      timeSong: {
        fontSize: 15
      },
      volumeWrapper: {
        width: theme.spacing(2),
        position: 'relative',
        marginLeft: theme.spacing(1.5),
        '&:hover $volumeSlider': {
          visibility: 'visible',
          opacity: 1
        }
      },
      volumeSlider: {
        height: 70,
        visibility: 'hidden',
        opacity: 0,
        transition: 'all 300ms ease',
        position: 'absolute',
        left: theme.spacing(-0.5),
        bottom: theme.spacing(4.5)
      },
      volumeSliderItem: {
        color: '#fff',
        '& .MuiSlider-thumb': {
          width: '15px',
          height: '15px',
          boxShadow: 'none !important',
          color: '#fff'
        }
      },
      progress: {
        flex: 1,
        margin: theme.spacing(0, 1, 1),
        position: 'absolute',
        bottom: 0
      },
      hide: {
        display: 'none!important'
      },
      visibility: {
        visibility: 'hidden'
      },
      disabledIcon: {
        color: `${theme.palette.grey[500]}!important`
      }
    }),
  { name: 'Waveform' }
);

const renderArray = (array: any[]) => {
  return Array.from(Array(array?.length).keys());
};

const formWaveSurferOptions = ref => ({
  container: ref,
  waveColor: '#eee',
  progressColor: '#2682d5',
  cursorColor: 'transparent',
  barWidth: 3,
  barHeight: 2,
  barRadius: 0,
  barGap: 2,
  responsive: true,
  height: 150,
  loopSelection: true,
  normalize: true,
  partialRender: false,
  minPxPerSec: 100
});

export default function Waveform({
  url,
  isPlaylist,
  isPlaying,
  setIsPlaying,
  songs: lists,
  selectedSong,
  setSelectedSong,
  autoPlay
}) {
  const contextAudio = new AudioContext();
  const classes = useStyles();
  const waveformRef = useRef(null);
  const wavesurfer = useRef(null);
  const [playing, setPlay] = useState(false);
  const [mute, setMute] = useState(false);
  const orderPlay = useRef([]);
  const [repeatSong, setRepeatSong] = useState(false);
  const [repeatList, setRepeatList] = useState(false);
  const [currentTime, setCurrentTime] = useState('0:00');
  const [songDuration, setSongDuration] = useState('0:00');
  const [volume, setVolume] = useState(0.5);
  const [shuffle, setShuffle] = useState<boolean>(false);
  const [isReady, setIsReady] = useState<boolean>(false);
  const { dispatch, useLoggedIn, i18n } = useGlobal();
  const songs = useSelector((state: GlobalState) =>
    getItemsSelector(state, lists)
  );

  const isLoggedIn = useLoggedIn();

  useEffect(() => {
    setPlay(false);
    setIsReady(false);

    const options = formWaveSurferOptions(waveformRef.current);

    wavesurfer.current = WaveSurfer.create(options);

    if (!url) return;

    wavesurfer.current.load(url);
    setCurrentTime('0:00');

    waveformRef.current.addEventListener('pointermove', e => {
      const hover = document.querySelector('#hover');

      hover.style.width = `${e.offsetX}px`;
    });

    wavesurfer.current.on('ready', () => {
      setIsReady(true);

      if (contextAudio.state === 'running' && !isPlaylist && autoPlay) {
        wavesurfer.current.play();
        setPlay(true);
      }

      if (isPlaylist) {
        setIsPlaying(isPlay => {
          if (isPlay) wavesurfer.current.play();

          return isPlay;
        });
      }

      wavesurfer.current.setVolume(mute ? 0 : volume);
      setVolume(volume);

      const time = formatTimeSong(wavesurfer.current.getDuration());
      setSongDuration(time);
    });

    wavesurfer.current.on('audioprocess', () => {
      const time = formatTimeSong(wavesurfer.current.getCurrentTime());
      setCurrentTime(time);
    });

    wavesurfer.current.on('seek', e => {
      const time = formatTimeSong(wavesurfer.current.getCurrentTime());
      setCurrentTime(time);
    });

    wavesurfer.current.on('finish', () => {
      updateTotalPlayItem();
      setRepeatSong(repeat => {
        if (repeat) {
          wavesurfer.current.play();
        } else {
          setShuffle(shuffle => {
            setRepeatList(repeatLists => {
              if (repeatLists && songs.length === 1) {
                wavesurfer.current.play();
              } else if (
                (!repeatLists && last(songs)?.id !== selectedSong?.id) ||
                repeatLists
              ) {
                changeSong(1);
              } else setIsPlaying(false);

              return repeatLists;
            });

            return shuffle;
          });

          setPlay(false);
        }

        return repeat;
      });
    });

    return () => wavesurfer.current.destroy();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [url]);

  const formatTimeSong = time => {
    const minutes = Math.floor(time / 60);
    const seconds = Math.floor(time - minutes * 60);
    const secondsFormat = `0${seconds}`.slice(-2);

    return `${minutes}:${secondsFormat}`;
  };

  const handlePlayPause = () => {
    if (isPlaylist) {
      setIsPlaying(!isPlaying);
    } else {
      setPlay(!playing);
      wavesurfer.current.playPause();
    }
  };

  React.useEffect(() => {
    if (wavesurfer.current?.isPlaying() !== isPlaying)
      wavesurfer.current.playPause();
  }, [isPlaying]);

  const updateTotalPlayItem = () => {
    if (!isLoggedIn) return;

    const identity = selectedSong?._identity;
    dispatch({ type: 'music/updateTotalPlayItem', payload: { identity } });
  };

  const handleVolumeChange = (event: any, newValue: number) => {
    wavesurfer.current.setVolume(newValue);
    setVolume(newValue);

    0 === newValue ? setMute(true) : setMute(false);
  };

  const handleVolumeMute = () => {
    setMute(prev => {
      wavesurfer.current.setVolume(prev ? volume : 0);

      return !prev;
    });
  };

  const handleRepeat = () => {
    if (!isPlaylist) {
      setRepeatSong(!repeatSong);

      return;
    }

    if (!repeatSong && !repeatList) {
      setRepeatList(true);
    }

    if (repeatList && !repeatSong) {
      setRepeatList(false);
      setRepeatSong(true);
    }

    if (repeatSong && !repeatList) {
      setRepeatSong(false);
    }
  };

  const handleShuffle = () => {
    setShuffle(prev => !prev);
  };

  React.useEffect(() => {
    if (isEmpty(songs)) return;

    if (shuffle) {
      orderPlay.current = renderArray(songs).sort(
        (a, b) => 0.5 - Math.random()
      );
    } else {
      orderPlay.current = renderArray(songs);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [shuffle, lists]);

  const changeSong = value => {
    if (!isPlaylist) return;

    setIsPlaying(true);

    const index = songs.findIndex(song => song?.id === selectedSong?.id);

    const indexShuffle = orderPlay.current.findIndex(order => index === order);

    setShuffle(shuffle => {
      if (shuffle) {
        if (indexShuffle + value === orderPlay.current.length)
          setSelectedSong(songs[orderPlay.current[0]]);
        else if (indexShuffle + value < 0)
          setSelectedSong(songs[last(orderPlay.current)]);
        else setSelectedSong(songs[orderPlay.current[indexShuffle + value]]);
      } else {
        if (index + value === songs?.length) {
          setSelectedSong(songs[0]);
        } else if (index + value < 0) setSelectedSong(last(songs));
        else setSelectedSong(songs[index + value]);
      }

      return shuffle;
    });
  };

  const textTooltip = () => {
    if (!repeatList && !repeatSong) return 'turn_on_repeat';

    if (repeatList) return 'turn_on_repeat_one';

    return 'turn_off_repeat';
  };

  const iconPlay = isPlaylist ? isPlaying : playing;

  return (
    <div className={classes.root}>
      <div className={classes.btnControlWrapper}>
        {isPlaylist ? (
          <button className={classes.btnControl} onClick={() => changeSong(-1)}>
            <LineIcon icon="ico-play-prev" />
          </button>
        ) : null}
        <button
          onClick={handlePlayPause}
          className={clsx(classes.btnControl, classes.btnPlayPause)}
          disabled={!isReady}
        >
          {!iconPlay ? (
            <LineIcon
              icon="ico-play"
              className={!isReady && classes.disabledIcon}
            />
          ) : (
            <LineIcon icon="ico-pause" />
          )}
        </button>
        {isPlaylist ? (
          <button className={classes.btnControl} onClick={() => changeSong(1)}>
            <LineIcon icon="ico-play-next" />
          </button>
        ) : null}
      </div>
      <div className={classes.timePlaying}>{currentTime}</div>
      <Box position="relative" flex={1}>
        <div
          ref={waveformRef}
          className={clsx(classes.waveForm, !isReady && classes.visibility)}
        >
          <div id="hover" className={clsx(classes.hover)}></div>
        </div>
        <LinearProgress
          color="inherit"
          className={clsx(isReady && classes.hide, classes.progress)}
        />
      </Box>

      <div className={classes.timeSong}>{songDuration}</div>
      {isPlaylist ? (
        <Tooltip
          title={i18n.formatMessage({
            id: shuffle ? 'turn_off_shuffle' : 'turn_on_shuffle'
          })}
        >
          <LineIcon
            icon="ico-shuffle"
            className={`${classes.btnShuffle} ${
              shuffle ? classes.activeIcon : ''
            }`}
            onClick={handleShuffle}
          />
        </Tooltip>
      ) : null}
      <Tooltip
        title={i18n.formatMessage({
          id: textTooltip()
        })}
      >
        <LineIcon
          icon={
            repeatSong && isPlaylist
              ? 'ico-play-repeat-one-o'
              : 'ico-play-repeat'
          }
          className={clsx(
            classes.btnRepeat,
            (repeatSong || repeatList) && classes.activeIcon,
            !isReady && classes.disabledIcon
          )}
          onClick={handleRepeat}
        />
      </Tooltip>

      <div className={classes.volumeWrapper}>
        {mute ? (
          <LineIcon
            icon="ico-volume-del"
            onClick={handleVolumeMute}
            className={!isReady && classes.disabledIcon}
          />
        ) : (
          <LineIcon
            icon="ico-volume-increase"
            onClick={handleVolumeMute}
            className={!isReady && classes.disabledIcon}
          />
        )}
        <div className={classes.volumeSlider}>
          {isReady && (
            <Slider
              orientation="vertical"
              value={mute ? 0 : volume}
              min={0}
              max={1}
              step={0.01}
              onChange={handleVolumeChange}
              aria-labelledby="vertical-slider"
              valueLabelDisplay="auto"
              valueLabelFormat={x => Math.round(x * 100)}
              className={classes.volumeSliderItem}
            />
          )}
        </div>
      </div>
    </div>
  );
}

import React from 'react';
import { Box, Slider, Tooltip, styled } from '@mui/material';
import { withStyles } from '@mui/styles';
import {
  Pause,
  PlayArrow,
  VolumeOff,
  VolumeUp,
  FullscreenExit,
  Fullscreen,
  Replay
} from '@mui/icons-material';
import { useGlobal } from '@metafox/framework';
import { isFunction } from 'lodash';
import SettingControl from './SettingControl';
import { formatTime } from './format';

interface ControlProps {
  [key: string]: any;
}

const name = 'control-video';

const RootControl = styled('div', { name, slot: 'root' })(({ theme }) => ({
  position: 'absolute',
  bottom: 0,
  right: 0,
  left: 0,
  transition: 'all 150ms ease',
  width: '100%',
  userSelect: 'none'
}));

const BottomContainer = styled('div', { name, slot: 'bottom' })(
  ({ theme }) => ({
    width: '100%',
    padding: theme.spacing(0, 2),
    maxHeight: '65%',
    backgroundImage:
      'linear-gradient(0deg, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.35) 40%, transparent)'
  })
);

const VolumeSlider = withStyles({
  root: {
    color: '#fff',
    width: '10px !important'
  },
  thumb: {
    width: '16px !important',
    height: '16px !important',
    boxShadow: 'none !important',
    color: '#fff'
  },
  track: {
    width: '10px !important'
  },
  rail: {
    color: '#fff'
  }
})(Slider);

const VolumeSliderWrapper = styled(Box, {
  name,
  slot: 'VolumeSliderWrapper',
  shouldForwardProp: props => props !== 'isHover'
})<{ isHover?: boolean }>(({ theme, isHover }) => ({
  height: 70,
  visibility: 'hidden',
  opacity: 0,
  transition: 'all 300ms ease',
  position: 'absolute',
  bottom: '100%',
  paddingBottom: theme.spacing(2),
  left: 0,
  ...(isHover && {
    visibility: 'visible',
    opacity: 1
  })
}));

const ButtonVolumeWrapper = styled(Box, { name, slot: 'ButtonVolume' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    position: 'relative',
    padding: theme.spacing(1)
  })
);

const PrettoSlider = withStyles({
  root: {
    padding: 0,
    height: '3px !important',
    color: '#fff !important',
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    flex: 1,
    '& .MuiSlider-thumb:is(:focus, :hover, $active, .Mui-active)': {
      display: 'inline-block !important'
    }
  },
  thumb: {
    height: '14px !important',
    width: '14px !important',
    backgroundColor: '#fff !important',
    border: '2px solid currentColor',
    marginTop: 0,
    marginLeft: 0,
    display: 'none !important',
    '&:focus, &:hover, &$active': {
      boxShadow: 'inherit !important'
    }
  },
  active: {},
  valueLabel: {
    left: 'calc(-50% + 4px)'
  },
  track: {
    border: '0 !important',
    height: '4px !important',
    borderRadius: 4,
    width: '100%'
  },
  rail: {
    height: '4px !important',
    borderRadius: 4
  }
})(Slider);

const SliderContainer = styled('div', { name, slot: 'slider' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    padding: theme.spacing(0, 2),
    flex: 1,
    paddingLeft: 0
  })
);

const ControlBox = styled('div', { name, slot: 'ControlBox' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between'
}));

const InnerControls = styled('div', {
  name,
  slot: 'InnerControls',
  shouldForwardProp(prop: string) {
    return !/visible/i.test(prop);
  }
})<{ visible: boolean }>(({ theme, visible }) => ({
  display: 'flex',
  padding: theme.spacing(1.25, 0),
  alignItems: 'center',
  width: '100%',
  ...(!visible && {
    '& > *': {
      opacity: 0
    }
  })
}));

const IconButtonStyled = styled('div', { name, slot: 'icon-button' })(
  ({ theme }) => ({
    color: '#fff !important',
    fontSize: theme.mixins.pxToRem(15),
    '&:hover': {
      cursor: 'pointer'
    }
  })
);

const ButtonWrapper = styled(Box, { name, slot: 'icon-button' })(
  ({ theme }) => ({
    padding: theme.spacing(1)
  })
);

const TimeSpan = styled('span', { name, slot: 'TimeSpan' })(({ theme }) => ({
  color: '#fff',
  fontSize: theme.mixins.pxToRem(12),
  '& > span': {
    minWidth: '40px',
    display: 'inline-block',
    '&:nth-of-type(1)': {
      textAlign: 'end'
    },
    '&:nth-of-type(2)': {
      textAlign: 'start'
    }
  }
}));

const ProgressWrapper = styled(Box, { name, slot: 'ProgressWrapper' })(
  ({ theme }) => ({
    position: 'relative',
    width: '100%',
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    flex: 1
  })
);

const Control = (props: ControlProps) => {
  const {
    onPlayPause,
    playing,
    played,
    onSeek,
    onSeekMouseUp,
    onVolumeChangeHandler,
    volume,
    mute,
    onMute,
    formatDuration,
    duration,
    currentTime,
    onMouseSeekDown,
    controlRef,
    loaded,
    onFullScreen,
    showFullScreen = false
  } = props || {};
  const { i18n, useIsMobile } = useGlobal();
  const isTablet = useIsMobile(true);
  const [isHoverVolume, setIsHoverVolume] = React.useState(false);
  const [visible, setVisible] = React.useState(true);
  const [markTimeline, setMarkTimeline] = React.useState('');
  const positionTimelineRef = React.useRef<{ x: number }>({
    x: 0
  });
  const poperTimelineRef = React.useRef(null);
  const timelineRef = React.useRef(null);

  const handleFullScreen = () => {
    isFunction(onFullScreen) && onFullScreen();
  };

  React.useImperativeHandle(controlRef, () => {
    return {
      setVisible: x => {
        setVisible(x);
      },
      visible
    };
  });

  const onMouseMoveTimeline = React.useCallback(
    (event: React.MouseEvent<HTMLDivElement>) => {
      try {
        const bounding = event.currentTarget.getBoundingClientRect();
        const mouseRelativeX =
          event.clientX - event.currentTarget.getBoundingClientRect().left;
        const hoverTime = formatTime(
          Math.max(0, (mouseRelativeX / bounding.width) * duration)
        );
        setMarkTimeline(hoverTime);
        positionTimelineRef.current = { x: event.clientX };

        if (poperTimelineRef.current != null) {
          poperTimelineRef.current.update();
        }
        // eslint-disable-next-line no-empty
      } catch (error) {}
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [setMarkTimeline, duration]
  );

  return (
    <RootControl ref={controlRef}>
      <BottomContainer>
        <ControlBox>
          <InnerControls visible={visible}>
            <IconButtonStyled onClick={onPlayPause}>
              {played === 1 ? (
                <Replay fontSize="medium" />
              ) : playing ? (
                <Pause fontSize="medium" />
              ) : (
                <PlayArrow fontSize="medium" />
              )}
            </IconButtonStyled>
            <SliderContainer>
              <TimeSpan>
                <span>{currentTime}</span> / <span>{formatDuration}</span>
              </TimeSpan>
              <ProgressWrapper>
                {/* main progress */}
                <Tooltip
                  title={markTimeline}
                  placement="top"
                  arrow
                  PopperProps={{
                    container: document.fullscreenElement ?? document.body,
                    popperRef: poperTimelineRef,
                    sx: {
                      pointerEvents: 'none',
                      display: visible ? undefined : 'none !important'
                    },
                    anchorEl: {
                      getBoundingClientRect: () => {
                        return new DOMRect(
                          positionTimelineRef.current.x,
                          timelineRef.current!.getBoundingClientRect().y + 10,
                          0,
                          0
                        );
                      }
                    }
                  }}
                >
                  <PrettoSlider
                    min={0}
                    max={100}
                    value={played * 100}
                    onChange={onSeek}
                    onChangeCommitted={onSeekMouseUp}
                    onMouseDown={onMouseSeekDown}
                    onMouseMove={onMouseMoveTimeline}
                    ref={timelineRef}
                  />
                </Tooltip>
                {/* progress loaded */}
                <PrettoSlider
                  disabled
                  min={0}
                  max={100}
                  value={loaded * 100}
                  sx={{ position: 'absolute', top: 0, left: 0, opacity: 0.3 }}
                />
              </ProgressWrapper>
            </SliderContainer>
            <SettingControl {...props} />
            <ButtonWrapper>
              <Tooltip
                title={i18n.formatMessage({
                  id: showFullScreen
                    ? 'exit_full_screen'
                    : 'switch_to_full_screen'
                })}
              >
                <IconButtonStyled onClick={handleFullScreen}>
                  {showFullScreen ? <FullscreenExit /> : <Fullscreen />}
                </IconButtonStyled>
              </Tooltip>
            </ButtonWrapper>
            {/* always show */}
            <ButtonVolumeWrapper
              sx={{ opacity: '1 !important' }}
              onMouseEnter={() => setIsHoverVolume(true)}
              onMouseLeave={() => setIsHoverVolume(false)}
            >
              {isTablet ? null : (
                <VolumeSliderWrapper isHover={isHoverVolume}>
                  <VolumeSlider
                    min={0}
                    max={1}
                    step={0.1}
                    orientation="vertical"
                    onChange={onVolumeChangeHandler}
                    value={volume}
                  />
                </VolumeSliderWrapper>
              )}
              <IconButtonStyled onClick={onMute}>
                {mute ? (
                  <VolumeOff fontSize="medium" />
                ) : (
                  <VolumeUp fontSize="medium" />
                )}
              </IconButtonStyled>
            </ButtonVolumeWrapper>
          </InnerControls>
        </ControlBox>
      </BottomContainer>
    </RootControl>
  );
};

export default Control;

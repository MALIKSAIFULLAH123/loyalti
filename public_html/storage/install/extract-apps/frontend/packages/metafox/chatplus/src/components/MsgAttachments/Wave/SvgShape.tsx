import { useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import React from 'react';
import { getPosX, ProgressUpdateInterval, throttle } from './utils';
import { isEmpty } from 'lodash';

interface TimePosInfo {
  currentTime: number;
  currentTimePos: number;
}

const name = 'SvgShape';

const SvgStyled = styled(Box, {
  name,
  slot: 'SvgShape',
  shouldForwardProp: props => props !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  display: 'flex',
  height: '50px',
  cursor: 'pointer',
  '& svg': {
    width: '100%',
    '&  > rect': {
      fill: isOwner ? '#ffffff4d' : '#0006',
      ...(theme.palette.mode === 'dark' && {
        fill: '#fff6'
      })
    }
  }
}));

function SvgShape(props: any) {
  const {
    percent,
    isOwner,
    audioRef,
    duration,
    setProgress,
    isDraggingProgress,
    setIsDraggingProgress
  } = props || {};

  const audio = audioRef.current || {};
  const progressBar = React.useRef<any>();
  const { useTheme } = useGlobal();
  const theme = useTheme();
  let timeOnMouseMove = 0;
  const [currentTimePos, setCurrentTimePos] = React.useState<number>(0);

  React.useEffect(() => {
    if (!currentTimePos) return;

    setProgress(currentTimePos);
  }, [currentTimePos, setProgress]);

  const getColorFill = index => {
    const totalChildEle =
      document.getElementById('svg-shape')?.children?.length * 2 - 1;

    if (!totalChildEle) return {};

    if (percent === 0 || percent === 100) return {};

    const totalItem = Math.ceil((totalChildEle * percent) / 100 / 2);

    return totalItem >= index
      ? {
          fill:
            theme.palette.mode === 'dark' ? '#fff' : isOwner ? '#fff' : '#000'
        }
      : {};
  };

  // Get time info while dragging indicator by mouse or touch
  const getCurrentProgress = (event: MouseEvent | TouchEvent): TimePosInfo => {
    const isSingleFileProgressiveDownload =
      audio.src?.indexOf('blob:') !== 0 && typeof duration === 'undefined';

    if (
      isSingleFileProgressiveDownload &&
      (!audio.src || !isFinite(audio.currentTime) || !progressBar.current)
    ) {
      return { currentTime: 0, currentTimePos: 0 };
    }

    const progressBarRect = progressBar.current.getBoundingClientRect();
    const maxRelativePos = progressBarRect.width;
    let relativePos = getPosX(event) - progressBarRect.left;

    if (relativePos < 0) {
      relativePos = 0;
    } else if (relativePos > maxRelativePos) {
      relativePos = maxRelativePos;
    }

    const currentTime = (duration * relativePos) / maxRelativePos;

    return {
      currentTime,
      currentTimePos: parseFloat(
        ((relativePos / maxRelativePos) * 100).toFixed(2)
      )
    };
  };

  const handleAudioTimeUpdate = throttle((e: Event): void => {
    const audio = e.target as HTMLAudioElement;

    if (isDraggingProgress.current) return;

    const { currentTime } = audio;

    if (currentTime === duration) {
      setCurrentTimePos(0);

      return;
    }

    setCurrentTimePos(
      parseFloat(((currentTime / duration) * 100 || 0).toFixed(2))
    );
  }, ProgressUpdateInterval);

  React.useEffect(() => {
    if (isEmpty(audio)) return;

    audio.addEventListener('timeupdate', handleAudioTimeUpdate, false);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [audio]);

  const handleMouseDownOrTouchStartProgressBar = (
    event: React.MouseEvent | React.TouchEvent
  ): void => {
    event.stopPropagation();
    const { currentTime, currentTimePos } = getCurrentProgress(
      event.nativeEvent
    );

    if (isFinite(currentTime)) {
      timeOnMouseMove = currentTime;
      setIsDraggingProgress(true);
      setCurrentTimePos(currentTimePos);

      if (event.nativeEvent instanceof MouseEvent) {
        window.addEventListener('mousemove', handleWindowMouseOrTouchMove);
        window.addEventListener('mouseup', handleWindowMouseOrTouchUp);
      } else {
        window.addEventListener('touchmove', handleWindowMouseOrTouchMove);
        window.addEventListener('touchend', handleWindowMouseOrTouchUp);
      }
    }
  };

  const handleWindowMouseOrTouchMove = (
    event: TouchEvent | MouseEvent
  ): void => {
    if (event instanceof MouseEvent) {
      event.preventDefault();
    }

    event.stopPropagation();
    // Prevent Chrome drag selection bug
    const windowSelection: Selection | null = window.getSelection();

    if (windowSelection && windowSelection.type === 'Range') {
      windowSelection.empty();
    }

    if (isDraggingProgress.current) {
      const { currentTime, currentTimePos } = getCurrentProgress(event);
      timeOnMouseMove = currentTime;
      setCurrentTimePos(currentTimePos);
    }
  };

  const handleWindowMouseOrTouchUp = (event: MouseEvent | TouchEvent): void => {
    event.stopPropagation();
    const newTime = timeOnMouseMove;
    const { onChangeCurrentTimeError, onSeek } = props;

    if (onSeek) {
      // handle seek data
    } else {
      const newProps: { isDraggingProgress: boolean; currentTimePos?: number } =
        {
          isDraggingProgress: false
        };

      if (
        !isEmpty(audio) &&
        (audio.readyState === audio.HAVE_NOTHING ||
          audio.readyState === audio.HAVE_METADATA ||
          !isFinite(newTime))
      ) {
        try {
          audio.load();
        } catch (err) {
          newProps.currentTimePos = 0;

          return (
            onChangeCurrentTimeError && onChangeCurrentTimeError(err as Error)
          );
        }
      }

      if (
        audio.readyState === audio.HAVE_NOTHING ||
        audio.readyState === audio.HAVE_METADATA ||
        !isFinite(duration)
      ) {
        try {
          audio.load();
          // eslint-disable-next-line no-empty
        } catch (err) {
          return;
        }
      }

      audio.currentTime = newTime;

      setIsDraggingProgress(newProps.isDraggingProgress);
      setCurrentTimePos(newProps.currentTimePos);
    }

    if (event instanceof MouseEvent) {
      window.removeEventListener('mousemove', handleWindowMouseOrTouchMove);
      window.removeEventListener('mouseup', handleWindowMouseOrTouchUp);
    } else {
      window.removeEventListener('touchmove', handleWindowMouseOrTouchMove);
      window.removeEventListener('touchend', handleWindowMouseOrTouchUp);
    }
  };

  return (
    <SvgStyled
      isOwner={isOwner}
      onMouseDown={handleMouseDownOrTouchStartProgressBar}
      onTouchStart={handleMouseDownOrTouchStartProgressBar}
    >
      <svg ref={progressBar} id="svg-shape" aria-hidden="true">
        <rect
          height="14"
          rx="3"
          width="3"
          x="0"
          y="calc(50% - 7px)"
          style={getColorFill(1)}
        ></rect>
        <rect
          height="29"
          rx="3"
          width="3"
          x="6"
          y="calc(50% - 14.5px)"
          style={getColorFill(2)}
        ></rect>
        <rect
          height="43"
          rx="3"
          width="3"
          x="12"
          y="calc(50% - 21.5px)"
          style={getColorFill(3)}
        ></rect>
        <rect
          height="13"
          rx="3"
          width="3"
          x="18"
          y="calc(50% - 6.5px)"
          style={getColorFill(4)}
        ></rect>
        <rect
          height="35"
          rx="3"
          width="3"
          x="24"
          y="calc(50% - 17.5px)"
          style={getColorFill(5)}
        ></rect>
        <rect
          height="39"
          rx="3"
          width="3"
          x="30"
          y="calc(50% - 19.5px)"
          style={getColorFill(6)}
        ></rect>
        <rect
          height="6"
          rx="3"
          width="3"
          x="36"
          y="calc(50% - 3px)"
          style={getColorFill(7)}
        ></rect>
        <rect
          height="12"
          rx="3"
          width="3"
          x="42"
          y="calc(50% - 6px)"
          style={getColorFill(8)}
        ></rect>
        <rect
          height="19"
          rx="3"
          width="3"
          x="48"
          y="calc(50% - 9.5px)"
          style={getColorFill(9)}
        ></rect>
        <rect
          height="4"
          rx="3"
          width="3"
          x="54"
          y="calc(50% - 2px)"
          style={getColorFill(10)}
        ></rect>
        <rect
          height="44"
          rx="3"
          width="3"
          x="60"
          y="calc(50% - 22px)"
          style={getColorFill(11)}
        ></rect>
        <rect
          height="30"
          rx="3"
          width="3"
          x="66"
          y="calc(50% - 15px)"
          style={getColorFill(12)}
        ></rect>
        <rect
          height="22"
          rx="3"
          width="3"
          x="72"
          y="calc(50% - 10px)"
          style={getColorFill(13)}
        ></rect>
        <rect
          height="32"
          rx="3"
          width="3"
          x="78"
          y="calc(50% - 16px)"
          style={getColorFill(14)}
        ></rect>
      </svg>
    </SvgStyled>
  );
}

export default SvgShape;

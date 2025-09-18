import { useGlobal } from '@metafox/framework';
import { LineIcon, Tooltip } from '@metafox/ui';
import React, { useCallback } from 'react';
import { IconBtn } from './ActionList';
import useTourGuideContext from '@metafox/tourguide/hooks';
import { PauseStatus, TIME_AUTOPLAY_FALLBACK } from '@metafox/tourguide';
import { isEmpty } from 'lodash';

function PlayPauseAction({ handleDraw, onClose }) {
  const { i18n } = useGlobal();

  const timeRef = React.useRef(0);
  const clock = React.useRef<any>();
  const stepRef = React.useRef<any>(0);

  const [timeState, setTimeState] = React.useState<number>(0);

  const { fire, step, steps, totalStep, pauseStatus } = useTourGuideContext();

  const stepItem = steps[step];
  const initialTime = stepItem?.delay || TIME_AUTOPLAY_FALLBACK;

  const callback = () => {
    clearInterval(clock.current);

    clock.current = setInterval(() => {
      timeRef.current = timeRef.current + 0.1;
      setTimeState(prev => prev + 0.1);

      if (timeRef.current >= initialTime) {
        stepRef.current = step + 1;
        handleDraw(stepRef.current);
      }
    }, 100);
  };

  const start = () => {
    if (isEmpty(stepItem)) return;

    timeRef.current = 0;
    setTimeState(0);
    clearInterval(clock.current);
    stepRef.current = 0;

    fire({
      type: 'setPlayPause',
      payload: PauseStatus.No
    });

    callback();
  };

  const pause = () => {
    clearInterval(clock.current);
  };

  const resume = () => {
    callback();
  };

  const clear = () => {
    timeRef.current = 0;
    setTimeState(0);

    fire({
      type: 'setPlayPause',
      payload: PauseStatus.No
    });

    clearInterval(clock.current);
  };

  React.useEffect(() => {
    if (pauseStatus !== PauseStatus.No) {
      pause();

      return;
    }

    resume();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pauseStatus]);

  const handleClick = useCallback(() => {
    fire({
      type: 'setPlayPause',
      payload:
        pauseStatus === PauseStatus.No ? PauseStatus.Pause : PauseStatus.No
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pauseStatus]);

  React.useEffect(() => {
    if (isEmpty(stepItem)) return;

    start();

    return () => {
      clear();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [stepItem]);

  React.useEffect(() => {
    const checkEndTime =
      timeRef.current >= initialTime - 0.1 && timeState >= initialTime - 0.1;

    if (step === totalStep - 1 && checkEndTime) {
      onClose(true);
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [initialTime, totalStep, step, timeState]);

  return (
    <Tooltip
      title={i18n.formatMessage({
        id:
          pauseStatus === PauseStatus.No
            ? 'tourguide_next_pause'
            : 'tourguide_next_play'
      })}
    >
      <IconBtn isPlaying={pauseStatus === PauseStatus.No} onClick={handleClick}>
        <LineIcon
          icon={pauseStatus === PauseStatus.No ? 'ico-pause' : 'ico-play'}
        />
      </IconBtn>
    </Tooltip>
  );
}

export default PlayPauseAction;

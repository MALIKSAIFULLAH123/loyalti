import { useGlobal } from '@metafox/framework';
import { TIME_NEXT_STORY_DEFAULT } from '@metafox/story/constants';
import { useStory, useStoryViewContext } from '@metafox/story/hooks';
import { PauseStatus, StoryUserProps } from '@metafox/story/types';
import { Box, LinearProgress, styled } from '@mui/material';
import { isEmpty, isEqual } from 'lodash';
import React from 'react';

const name = 'progress-story';

const ProgressWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  width: '100%'
}));

const ProgessStory = styled(Box, { name })(({ theme }) => ({
  width: '100%',
  marginRight: theme.spacing(0.5),
  '&:last-child': {
    marginRight: theme.spacing(0)
  }
}));

const LinearProgressStyled = styled(LinearProgress, { name })(({ theme }) => ({
  borderRadius: theme.shape.borderRadius / 4,
  backgroundColor: 'rgba(255, 255, 255, 0.5)',
  '& .MuiLinearProgress-bar': {
    borderRadius: theme.shape.borderRadius / 2,
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    transition: 'transform .3s linear',
    transformOrigin: 'left',
    willChange: 'transform'
  }
}));

interface Props {
  identity: string;
  hasNext?: boolean;
}

function Progress({ identity }: Props) {
  const { useGetItem, useGetItems, getAcl, navigate } = useGlobal();
  const createAcl = getAcl('story.story.create');

  const { hasNext } = useStory();

  const storyRef = React.useRef<any>(0);

  const [timeState, setTimeState] = React.useState<number>(0);

  const clock = React.useRef<any>();
  const timeRef = React.useRef(0);

  const contextStory = useStoryViewContext();
  const {
    identityUserStoryActive,
    identityStoryActive,
    pauseStatus,
    indexStoryActive,
    listUserStories,
    readyStateFile,
    progressVideoPlay,
    fire,
    durationVideo
  } = contextStory || {};

  const storyActive = useGetItem(identity || identityStoryActive);
  const { duration, type, in_process } = storyActive || {};
  const initialTime = durationVideo || duration || TIME_NEXT_STORY_DEFAULT;
  const isTypeVideo =
    (type === 'video' && in_process) || type === 'photo' || type === 'text'
      ? false
      : true;

  const userStoryActive = useGetItem(identityUserStoryActive);

  const stories = useGetItems(userStoryActive?.stories) as StoryUserProps[];

  const prevProgressVideoPlay = React.useRef<any>(0);

  React.useEffect(() => {
    if (!isTypeVideo) return;

    timeRef.current = progressVideoPlay;
  }, [isTypeVideo, progressVideoPlay]);

  const callback = () => {
    if (isEmpty(storyActive)) return;

    clearInterval(clock.current);

    if (isTypeVideo) {
      clock.current = setInterval(() => {
        if (
          timeRef.current === 0 ||
          isEqual(timeRef.current, prevProgressVideoPlay.current)
        )
          return;

        prevProgressVideoPlay.current = timeRef.current;
        setTimeState(prev => prev + 0.1);

        if (
          parseFloat(parseFloat(timeRef.current).toFixed(1)) >=
          parseFloat(parseFloat(initialTime).toFixed(1))
        ) {
          storyRef.current = indexStoryActive + 1;
          fire({
            type: 'setIdentityStoryActive',
            payload: stories?.[storyRef.current]?._identity
          });
        }
      }, 100);

      return;
    }

    clock.current = setInterval(() => {
      timeRef.current = timeRef.current + 0.1;
      setTimeState(prev => prev + 0.1);

      if (timeRef.current >= initialTime) {
        storyRef.current = indexStoryActive + 1;
        fire({
          type: 'setIdentityStoryActive',
          payload: stories?.[storyRef.current]?._identity
        });
      }
    }, 100);
  };

  const start = () => {
    timeRef.current = 0;
    setTimeState(0);
    clearInterval(clock.current);

    if (pauseStatus === PauseStatus.Force) return;

    fire({
      type: 'setForcePause',
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
      type: 'setProgressVideoPlay',
      payload: 0
    });
    fire({
      type: 'setForcePause',
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

  React.useEffect(() => {
    const checkNextStory = isTypeVideo
      ? parseFloat(parseFloat(timeRef.current).toFixed(1)) >=
        parseFloat(parseFloat(initialTime).toFixed(1))
      : timeState >= initialTime - 0.1;

    if (indexStoryActive === stories.length - 1 && checkNextStory) {
      if (!hasNext) {
        if (createAcl) {
          fire({ type: 'setLastStory', payload: true });

          return;
        }

        navigate('/');

        return;
      }

      const findIndex = listUserStories?.findIndex(
        item => userStoryActive?.id === item?.id
      );

      if (listUserStories[findIndex + 1]) {
        fire({
          type: 'setIdentityUserS_Active',
          payload: listUserStories[findIndex + 1]?._identity
        });
      }

      storyRef.current = 0;

      return;
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    timeState,
    indexStoryActive,
    storyActive?.id,
    stories,
    listUserStories,
    userStoryActive?.id,
    hasNext,
    isTypeVideo,
    initialTime
  ]);

  React.useEffect(() => {
    if (!stories?.length) return;

    clear();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [userStoryActive?.id]);

  React.useEffect(() => {
    if (!readyStateFile || !storyActive) return;

    start();

    return () => {
      clear();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [storyActive?.id, readyStateFile]);

  const calculatorProgress = React.useCallback(
    index => {
      if (index < indexStoryActive) return 100;

      if (index === indexStoryActive) {
        if (timeState >= initialTime) return;

        return Math.round((timeState / initialTime) * 100) || 0;
      }

      if (index > indexStoryActive) return 0;
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [timeState, indexStoryActive]
  );

  return (
    <ProgressWrapper data-testid="progressStoryBlock">
      {stories.map((item, index) => (
        <ProgessStory key={item?.id}>
          <LinearProgressStyled
            variant="determinate"
            value={calculatorProgress(index)}
          />
        </ProgessStory>
      ))}
    </ProgressWrapper>
  );
}

export default Progress;

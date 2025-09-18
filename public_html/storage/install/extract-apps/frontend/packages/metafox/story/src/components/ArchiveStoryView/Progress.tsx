import { useGlobal } from '@metafox/framework';
import { TIME_NEXT_STORY_DEFAULT } from '@metafox/story/constants';
import { useArchiveStory, useStoryViewContext } from '@metafox/story/hooks';
import { shouldPreload } from '@metafox/story/hooks/connectStoryArchive';
import { PauseStatus } from '@metafox/story/types';
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
    transition: 'transform 0.3s linear',
    transformOrigin: 'left',
    willChange: 'transform'
  }
}));

interface Props {
  identity: string;
  hasNext?: boolean;
}

function Progress({ identity }: Props) {
  const { useGetItem, useGetItems, dispatch, usePageParams } = useGlobal();

  const pageParams = usePageParams();
  const storyRef = React.useRef<any>(0);

  const [timeState, setTimeState] = React.useState<number>(0);

  const clock = React.useRef<any>();
  const timeRef = React.useRef(0);

  const contextStory = useStoryViewContext();

  const { handleNext, hasNext } = useArchiveStory();
  const {
    identityStoryActive,
    pauseStatus,
    indexStoryActive,
    readyStateFile,
    progressVideoPlay,
    fire,
    durationVideo,
    total,
    stories: storieEntities,
    pagingId,
    loading,
    positionStory
  } = contextStory || {};

  const stories = useGetItems(storieEntities);

  const storyActive = useGetItem(identityStoryActive);
  const { duration, type, in_process } = storyActive || {};
  const initialTime = durationVideo || duration || TIME_NEXT_STORY_DEFAULT;
  const isTypeVideo =
    (type === 'video' && in_process) || type === 'photo' || type === 'text'
      ? false
      : true;

  const prevProgressVideoPlay = React.useRef<any>(0);

  React.useEffect(() => {
    if (!isTypeVideo) return;

    timeRef.current = progressVideoPlay;
  }, [isTypeVideo, progressVideoPlay]);

  const loadmore = position => {
    const handleSuccessLoadMore = pagingData => {
      const pagingId = pagingData?.pagingId;
      const stories = pagingData?.ids;

      if (!pagingId || !stories?.length) return;

      const total = pagingData?.pagesOffset?.total;

      fire({
        type: 'setInit',
        payload: {
          stories,
          total
        }
      });
    };

    const direction = shouldPreload(contextStory?.stories?.length, position);

    if (!direction) return;

    dispatch({
      type: 'story/story_archive/LOAD',
      payload: {
        user_id: pageParams?.user_id,
        date: contextStory?.date,
        direction
      },
      meta: {
        onSuccess: handleSuccessLoadMore
      }
    });
  };

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

          if (!stories?.[storyRef.current]) return;

          loadmore(storyRef.current);
          fire({
            type: 'setStoryActive',
            payload: {
              identity: stories?.[storyRef.current]?._identity,
              index: storyRef.current,
              positionStory: positionStory + 1
            }
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

        if (!stories?.[storyRef.current]) return;

        loadmore(storyRef.current);
        fire({
          type: 'setStoryActive',
          payload: {
            identity: stories?.[storyRef.current]?._identity,
            index: storyRef.current,
            positionStory: positionStory + 1
          }
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
    if (loading) return;

    const checkNextStory = isTypeVideo
      ? parseFloat(parseFloat(timeRef.current).toFixed(1)) >=
        parseFloat(parseFloat(initialTime).toFixed(1))
      : timeState >= initialTime - 0.1;

    if (indexStoryActive === stories.length - 1 && checkNextStory) {
      storyRef.current = 0;

      if (hasNext) {
        handleNext();

        return;
      }

      fire({
        type: 'setForcePause',
        payload: PauseStatus.Force
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    loading,
    timeState,
    indexStoryActive,
    storyActive?.id,
    stories,
    hasNext,
    isTypeVideo,
    initialTime
  ]);

  React.useEffect(() => {
    if (loading || pauseStatus !== PauseStatus.No) {
      pause();

      return;
    }

    resume();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pauseStatus, loading]);

  React.useEffect(() => {
    if (!stories?.length) return;

    clear();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pagingId]);

  React.useEffect(() => {
    if (!readyStateFile || !storyActive) return;

    start();

    return () => {
      clear();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [storyActive?.id, readyStateFile, stories.length]);

  const calculatorProgress = React.useCallback(
    index => {
      if (index < positionStory) return 100;

      if (index === positionStory) {
        if (timeState >= initialTime) return;

        return Math.round((timeState / initialTime) * 100) || 0;
      }

      if (index > positionStory) return 0;
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [timeState, positionStory]
  );

  return (
    <ProgressWrapper>
      {Array.from(Array(total).keys()).map((_, index) => (
        <ProgessStory key={index}>
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

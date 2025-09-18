import { useActionControl, useGlobal } from '@metafox/framework';
import {
  TIME_NEXT_STORY_DEFAULT,
  TYPE_LIVE_VIDEO
} from '@metafox/story/constants';
import {
  ItemActionMenu,
  LineIcon,
  PrivacyIcon,
  TruncateText,
  UserAvatar,
  UserName
} from '@metafox/ui';
import {
  Box,
  Button,
  IconButton,
  LinearProgress,
  Tooltip,
  styled
} from '@mui/material';
import React, { useRef } from 'react';
import FromNowStory from '../FromNowStory';
import { PauseStatus, StoryItemProps } from '@metafox/story/types';
import { isEmpty, isEqual } from 'lodash';
import { useStoryViewContext } from '@metafox/story/hooks';

const name = 'HeaderStory';

const RootStyled = styled(Box, { name })(({ theme }) => ({
  width: '100%',
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  flexDirection: 'column',
  padding: theme.spacing(1.5)
}));

const UserInfoWrapper = styled(Box, {
  name,
  shouldForwardProp: props => props !== 'isTypeLiveVideo'
})<{ isTypeLiveVideo?: boolean }>(({ theme, isTypeLiveVideo }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  padding: theme.spacing(1.5, 0),
  ...(isTypeLiveVideo && {
    paddingBottom: theme.spacing(1)
  })
}));

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

const WrapperTitle = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  maxWidth: '70%',
  overflow: 'hidden'
}));

const ActionWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));

const IconButtonStyled = styled(IconButton)(({ theme }) => ({
  color: '#fff'
}));

const PrivacyBlockStyled = styled('div', { name, slot: 'PrivacyBlockStyled' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'row',
    alignItems: 'center',
    fontSize: '0.8125rem',
    paddingTop: '0.25em'
  })
);

const SeparateSpanStyled = styled('div', { name, slot: 'SeparateSpanStyled' })(
  ({ theme }) => ({
    color: 'rgba(255, 255, 255, 0.9)',
    display: 'flex',
    alignItems: 'center',
    '& span + span:before': {
      content: '"·"',
      display: 'inline-block',
      padding: `${theme.spacing(0, 0.5)}`
    }
  })
);

const ButtonStyled = styled(Button, { name, slot: 'ButtonStyled' })(
  ({ theme }) => ({
    alignSelf: 'flex-start',
    color: '#fff',
    fontWeight: theme.typography.fontWeightMedium,
    borderColor: '#fff',
    '&:hover': {
      borderColor: theme.palette.border.secondary
    }
  })
);

const ItemActionItem = React.memo(
  ({ identity, contextStory }: any) => {
    const { i18n, navigate } = useGlobal();
    const { pauseStatus, fire } = contextStory;

    const [handleActionLocal] = useActionControl<unknown, unknown>(
      identity,
      {}
    );

    const handleAction = (type: string, payload?: unknown, meta?: unknown) => {
      handleActionLocal(type, payload, meta);

      if (type.includes('deleteItem')) {
        navigate('/');
      }
    };

    const prevPauseRef = React.useRef<any>(pauseStatus);

    const [openMenu, setOpenMenu] = React.useState(false);

    const triggerOpenMenu = value => {
      setOpenMenu(value);
    };

    React.useEffect(() => {
      if (openMenu) {
        prevPauseRef.current = pauseStatus;

        fire({
          type: 'setForcePause',
          payload: PauseStatus.Force
        });

        return;
      }

      fire({
        type: 'setForcePause',
        payload: prevPauseRef.current
      });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [openMenu]);

    React.useEffect(() => {
      prevPauseRef.current = false;
      fire({
        type: 'setForcePause',
        payload: PauseStatus.No
      });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [identity]);

    return (
      <ItemActionMenu
        triggerOpen={triggerOpenMenu}
        identity={identity}
        handleAction={handleAction}
        control={
          <Tooltip
            title={i18n.formatMessage({ id: 'more_options' })}
            placement="bottom"
          >
            <IconButtonStyled size="medium" disableRipple disableFocusRipple>
              <LineIcon icon="ico-dottedmore" />
            </IconButtonStyled>
          </Tooltip>
        }
      />
    );
  },
  (prev, next) =>
    prev?.identity === next?.identity &&
    prev?.contextStory?.pauseStatus === next?.contextStory?.pauseStatus
);

interface Props {
  story?: StoryItemProps;
  isMinHeight?: boolean;
  isSmallHeight?: boolean;
}

function PlayHeaderStory({
  story,
  isMinHeight = false,
  isSmallHeight = false
}: Props) {
  const {
    i18n,
    useGetItem,
    usePageParams,
    useIsMobile,
    goSmartBack,
    navigate,
    useTheme
  } = useGlobal();
  const theme = useTheme();
  const isMobile = useIsMobile(true);
  const contextStory = useStoryViewContext();
  const {
    pauseStatus,
    fire,
    readyStateFile,
    progressVideoPlay,
    mutedStatus,
    durationVideo
  } = contextStory || {};

  const handleClose = () => {
    goSmartBack();
  };

  const params = usePageParams();
  const user = useGetItem(story?.user);

  const [timeState, setTimeState] = React.useState<number>(0);

  const clock = useRef<any>();
  const timeRef = useRef<number>(0);

  const {
    _identity,
    privacy,
    creation_date,
    duration,
    type,
    extra_params,
    in_process
  } = story || {};
  const { is_streaming } = extra_params || {};

  const initialTime = durationVideo || duration || TIME_NEXT_STORY_DEFAULT;
  const isTypeVideo =
    (type === 'video' && in_process) || type === 'photo' || type === 'text'
      ? false
      : true;
  const isTypeLiveVideo = type === TYPE_LIVE_VIDEO ? true : false;

  const prevProgressVideoPlay = React.useRef<any>(0);

  const handlePlay = () => {
    if (timeRef.current === 0 || timeRef.current >= initialTime) {
      start();

      return;
    }

    if (pauseStatus !== PauseStatus.No) {
      fire({
        type: 'setForcePause',
        payload: PauseStatus.No
      });

      return;
    }

    fire({
      type: 'setForcePause',
      payload: PauseStatus.Force
    });
  };

  const handleMuted = () => {
    fire({
      type: 'setMuted',
      payload: mutedStatus ? false : true
    });
  };

  React.useEffect(() => {
    if (!isTypeVideo) return;

    timeRef.current = progressVideoPlay;
  }, [isTypeVideo, progressVideoPlay]);

  const callback = () => {
    if (isEmpty(story)) return;

    clearInterval(clock.current);

    if (isTypeVideo) {
      clock.current = setInterval(() => {
        if (
          timeRef.current === 0 ||
          isEqual(timeRef.current, prevProgressVideoPlay.current)
        )
          return;

        prevProgressVideoPlay.current = timeRef.current;

        if (timeRef.current >= initialTime) {
          fire({
            type: 'setForcePause',
            payload: PauseStatus.Force
          });
          pause();
        }
      }, 100);

      return;
    }

    clock.current = setInterval(() => {
      timeRef.current = timeRef.current + 0.1;
      setTimeState(prev => prev + 0.1);

      if (timeRef.current >= initialTime) {
        fire({
          type: 'setForcePause',
          payload: PauseStatus.Force
        });
        pause();
      }
    }, 100);
  };

  const start = () => {
    timeRef.current = 0;
    setTimeState(0);
    clearInterval(clock.current);

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
    if (!readyStateFile || !params.id) return;

    start();

    return () => {
      clear();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [params?.id, readyStateFile]);

  const calculatorProgress = React.useCallback(
    () => {
      const time = isTypeVideo ? timeRef.current : timeState;

      if (!readyStateFile) return 0;

      if (time >= initialTime) return 100;

      return Math.round((time / initialTime) * 100) || 0;
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [timeRef.current, initialTime, readyStateFile, timeState]
  );

  const handleViewLiveVideo = () => {
    if (!isTypeLiveVideo || !story?.item_id) return;

    navigate(`/live-video/${story?.item_id}`);
  };

  return (
    <RootStyled>
      <ProgressWrapper>
        <ProgessStory>
          <LinearProgressStyled
            variant="determinate"
            value={calculatorProgress() || 0}
          />
        </ProgessStory>
      </ProgressWrapper>
      <UserInfoWrapper isTypeLiveVideo={isTypeLiveVideo}>
        <WrapperTitle>
          <UserAvatar
            user={user}
            size={isMinHeight || isSmallHeight ? 36 : 48}
            noStory
            showStatus={false}
          />
          {isSmallHeight ? null : (
            <Box ml={1} overflow="hidden">
              <TruncateText lines={1} style={{ color: '#fff' }} variant="h5">
                <UserName user={user} />
              </TruncateText>
              {isTypeLiveVideo ? (
                <TruncateText
                  lines={1}
                  variant="body1"
                  style={{
                    color: 'rgba(255, 255, 255, 0.9)',
                    fontSize: theme.mixins.pxToRem(13),
                    paddingTop: '0.25em'
                  }}
                >
                  {i18n.formatMessage({
                    id: is_streaming ? 'live_now' : 'recorded_live'
                  })}
                </TruncateText>
              ) : null}
              <PrivacyBlockStyled>
                <SeparateSpanStyled>
                  <FromNowStory value={creation_date} format="ll" />
                  <PrivacyIcon value={privacy} />
                </SeparateSpanStyled>
              </PrivacyBlockStyled>
            </Box>
          )}
        </WrapperTitle>
        <ActionWrapper>
          <IconButtonStyled size="medium" onClick={handlePlay}>
            {pauseStatus === PauseStatus.No ? (
              <Tooltip
                title={i18n.formatMessage({ id: 'pause' })}
                placement="bottom"
              >
                <LineIcon icon="ico-pause" />
              </Tooltip>
            ) : (
              <Tooltip
                title={i18n.formatMessage({ id: 'play' })}
                placement="bottom"
              >
                <LineIcon icon="ico-play" />
              </Tooltip>
            )}
          </IconButtonStyled>
          {isTypeVideo && !in_process ? (
            <IconButtonStyled size="medium" onClick={handleMuted}>
              {mutedStatus ? (
                <Tooltip
                  title={i18n.formatMessage({ id: 'unmute' })}
                  placement="bottom"
                >
                  <LineIcon icon="ico-volume-del" />
                </Tooltip>
              ) : (
                <Tooltip
                  title={i18n.formatMessage({ id: 'mute' })}
                  placement="bottom"
                >
                  <LineIcon icon="ico-volume-increase" />
                </Tooltip>
              )}
            </IconButtonStyled>
          ) : null}
          <ItemActionItem identity={_identity} contextStory={contextStory} />
          {isMobile ? (
            <IconButtonStyled onClick={handleClose}>
              <LineIcon icon="ico-close" />
            </IconButtonStyled>
          ) : null}
        </ActionWrapper>
      </UserInfoWrapper>
      {isTypeLiveVideo ? (
        <ButtonStyled
          role="button"
          data-testid="buttonWatchVideo"
          variant="outlined"
          size="small"
          disableRipple
          onClick={handleViewLiveVideo}
        >
          {i18n.formatMessage({
            id: is_streaming ? 'watch_live_video' : 'watch_video'
          })}
        </ButtonStyled>
      ) : null}
    </RootStyled>
  );
}

export default PlayHeaderStory;

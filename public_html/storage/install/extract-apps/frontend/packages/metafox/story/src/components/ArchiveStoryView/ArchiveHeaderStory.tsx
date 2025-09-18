import { useActionControl, useGlobal } from '@metafox/framework';
import {
  STORY_ARCHIVE_PAGINATION_DELETE,
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
import { Box, Button, IconButton, Tooltip, styled } from '@mui/material';
import React, { useRef } from 'react';
import FromNowStory from '../FromNowStory';
import { PauseStatus, StoryItemProps } from '@metafox/story/types';
import { useStoryViewContext } from '@metafox/story/hooks';
import Progress from './Progress';

const name = 'ArchiveHeaderStory';

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
    const { i18n, goSmartBack, dispatch } = useGlobal();
    const { pauseStatus, fire, pagingId, identityStoryActive, openActionItem } =
      contextStory;

    const [handleActionLocal] = useActionControl<unknown, unknown>(
      identity,
      {}
    );

    const handleAction = (type: string, payload?: unknown, meta?: any) => {
      if (type.includes('deleteItem')) {
        dispatch({
          type: STORY_ARCHIVE_PAGINATION_DELETE,
          payload: { identity: identityStoryActive, pagingId }
        });
      }

      handleActionLocal(type, payload, {
        ...meta,
        onSuccess: () => goSmartBack()
      });
    };

    const [openMenu, setOpenMenu] = React.useState(false);

    const triggerOpenMenu = value => {
      setOpenMenu(value);
    };

    React.useEffect(() => {
      if (pauseStatus === PauseStatus.Force || openActionItem) return;

      fire({
        type: 'setForcePause',
        payload: openMenu ? PauseStatus.Pause : PauseStatus.No
      });

      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [openMenu, openActionItem]);

    React.useEffect(() => {
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
        tabIndex={1}
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
    prev?.contextStory?.pauseStatus === next?.contextStory?.pauseStatus &&
    prev?.contextStory === next?.contextStory
);

interface Props {
  story?: StoryItemProps;
  isMinHeight?: boolean;
  isSmallHeight?: boolean;
}

function ArchiveHeaderStory({
  story,
  isMinHeight = false,
  isSmallHeight = false
}: Props) {
  const { i18n, useGetItem, useIsMobile, goSmartBack, navigate, useTheme } =
    useGlobal();
  const theme = useTheme();
  const isMobile = useIsMobile(true);
  const contextStory = useStoryViewContext();
  const { pauseStatus, fire, progressVideoPlay, mutedStatus } =
    contextStory || {};

  const handleClose = () => {
    goSmartBack();
  };

  const user = useGetItem(story?.user);

  const timeRef = useRef<number>(0);

  const {
    _identity,
    privacy,
    creation_date,

    type,
    extra_params,
    in_process
  } = story || {};
  const { is_streaming } = extra_params || {};

  const isTypeVideo =
    (type === 'video' && in_process) || type === 'photo' || type === 'text'
      ? false
      : true;
  const isTypeLiveVideo = type === TYPE_LIVE_VIDEO ? true : false;

  const handlePlay = () => {
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

  const handleViewLiveVideo = () => {
    if (!isTypeLiveVideo || !story?.item_id) return;

    navigate(`/live-video/${story?.item_id}`);
  };

  return (
    <RootStyled>
      <ProgressWrapper>
        <Progress identity={_identity} />
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

export default ArchiveHeaderStory;

import { useGlobal } from '@metafox/framework';
import { useStoryViewContext } from '@metafox/story/hooks';
import { PauseStatus, StoryItemProps } from '@metafox/story/types';
import {
  LineIcon,
  PrivacyIcon,
  TruncateText,
  UserAvatar,
  UserName
} from '@metafox/ui';
import { Box, Button, IconButton, Tooltip, styled } from '@mui/material';
import React, { memo } from 'react';
import FromNowStory from '../FromNowStory';
import { ItemActionItem } from './ItemActionItem';
import Progress from './Progress';
import { TYPE_LIVE_VIDEO } from '@metafox/story/constants';
import { camelCase } from 'lodash';

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
    fontSize: theme.mixins.pxToRem(13),
    paddingTop: '0.25em'
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

interface IProps {
  identity: string;
  isMinHeight?: boolean;
  isSmallHeight?: boolean;
}

export interface DeleteSuccessType {
  nextStory?: boolean;
}

function HeaderStory({
  identity,
  isMinHeight = false,
  isSmallHeight = false
}: IProps) {
  const {
    navigate,
    i18n,
    useIsMobile,
    goSmartBack,
    useGetItem,
    useGetItems,
    useTheme,
    dispatch
  } = useGlobal();
  const theme = useTheme();
  const isMobile = useIsMobile(true);

  const handleClose = () => {
    goSmartBack();
  };

  const contextStory = useStoryViewContext();
  const {
    identityUserStoryActive,
    identityStoryActive,
    pauseStatus,
    mutedStatus = true,
    indexStoryActive,
    listUserStories,
    fire
  } = contextStory || {};

  const userStoryActive = useGetItem(identityUserStoryActive);
  const stories = useGetItems(userStoryActive?.stories) as StoryItemProps[];
  const story = useGetItem(identity || identityStoryActive);

  const {
    _identity,
    privacy,
    modification_date,
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

    dispatch({
      type: 'story/updateMutedStatus',
      payload: mutedStatus ? false : true
    });
  };

  const onDeleteSuccess = React.useCallback(
    ({ nextStory }: DeleteSuccessType) => {
      const isLastStory =
        userStoryActive?.stories.length - 1 === indexStoryActive || nextStory;

      const userStoryIndex = listUserStories?.findIndex(
        item => item?.id === userStoryActive?.id
      );

      const isLastUsernLastStory =
        listUserStories?.length - 1 === userStoryIndex && isLastStory;

      if (isLastUsernLastStory) {
        navigate('/');

        return;
      }

      if (isLastStory) {
        fire({
          type: 'setIdentityUserS_Active',
          payload: listUserStories[userStoryIndex + 1]?._identity
        });

        return;
      }

      fire({
        type: 'setIdentityStoryActive',
        payload: stories?.[indexStoryActive + 1]?._identity
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [
      userStoryActive?.stories,
      userStoryActive?.id,
      indexStoryActive,
      listUserStories,
      stories
    ]
  );

  const handleViewLiveVideo = () => {
    if (!isTypeLiveVideo || !story?.item_id) return;

    navigate(`/live-video/${story?.item_id}`);
  };

  return (
    <RootStyled data-testid="headerStoryBlock">
      <Progress identity={identity} />
      <UserInfoWrapper isTypeLiveVideo={isTypeLiveVideo}>
        <WrapperTitle>
          <UserAvatar
            user={userStoryActive}
            size={isMinHeight || isSmallHeight ? 36 : 48}
            noStory
            showLiveStream
            showStatus={false}
          />
          {isSmallHeight ? null : (
            <Box ml={1} overflow="hidden">
              <TruncateText lines={1} style={{ color: '#fff' }} variant="h5">
                <UserName user={userStoryActive} />
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
                  <FromNowStory value={modification_date} shorten />
                  <PrivacyIcon value={privacy} />
                </SeparateSpanStyled>
              </PrivacyBlockStyled>
            </Box>
          )}
        </WrapperTitle>
        <ActionWrapper data-testid="actionStoryBlock">
          <IconButtonStyled
            data-testid={camelCase('icon button')}
            size="smaller"
            onClick={handlePlay}
          >
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
          <ItemActionItem
            identity={_identity}
            contextStory={contextStory}
            onDeleteSuccess={onDeleteSuccess}
          />
          {isMobile ? (
            <IconButtonStyled onClick={handleClose} size="medium">
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

export default memo(
  HeaderStory,
  (prev, next) => prev.identity === next.identity
);

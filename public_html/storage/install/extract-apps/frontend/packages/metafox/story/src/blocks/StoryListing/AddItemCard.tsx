import { useGlobal, useIsMobile } from '@metafox/framework';
import { SIZE_AVATAR_TYPE } from '@metafox/story/constants';
import { ViewFeedStatus } from '@metafox/story/types';
import { LineIcon, UserAvatar } from '@metafox/ui';
import { colorHash, getImageSrc, shortenFullName } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';

const name = 'AddItemCard';

const RootStyled = styled(Box, {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  // flexBasis: '140px',
  // flexShrink: 0,
  width: '140px',
  height: '250px',
  marginRight: theme.spacing(1),
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  position: 'relative',
  background: theme.palette.background.paper,
  cursor: 'pointer',
  border: theme.mixins.border('secondary')
}));

const RootAvatarStyled = styled(Box, {
  name,
  slot: 'RootAvatar'
})(({ theme }) => ({
  marginRight: theme.spacing(2),
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  position: 'relative',
  cursor: 'pointer',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-start'
}));
const WrapperAvatar = styled(Box, {
  name,
  slot: 'WrapperAvatar',
  shouldForwardProp: props => props !== 'hasLiveVideo'
})<{ hasLiveVideo?: boolean }>(({ theme, hasLiveVideo }) => ({
  marginBottom: theme.spacing(1),
  position: 'relative',
  ...(hasLiveVideo && {
    marginBottom: 0
  })
}));
const ImageStyled = styled('img', { name, slot: 'img' })(({ theme }) => ({
  width: '100%',
  height: 'calc(100% - 50px)',
  objectFit: 'cover'
}));

const ImageBackground = styled('div', { name, slot: 'background' })(
  ({ theme }) => ({
    width: '100%',
    height: 'calc(100% - 50px)',
    color: '#fff',
    fontSize: theme.mixins.pxToRem(28),
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center'
  })
);

const NewStoryText = styled(Box, { name, slot: 'NewStory' })(({ theme }) => ({
  position: 'absolute',
  bottom: 0,
  height: '50px',
  width: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  textAlign: 'center',
  fontWeight: theme.typography.fontWeightSemiBold
}));

const ButtonAddMobile = styled(Box, { name, slot: 'ButtonAdd-mobile' })(
  ({ theme }) => ({
    position: 'absolute',
    bottom: 0,
    right: 0,
    width: '26px',
    height: '26px',
    backgroundColor: theme.palette.primary.main,
    borderRadius: '50%',
    cursor: 'pointer',
    borderStyle: 'solid',
    borderColor: theme.palette.background.paper,
    borderWidth: '1.5px',
    zIndex: 99
  })
);

const ButtonAddStyled = styled(Box, { name, slot: 'ButtonAdd' })(
  ({ theme }) => ({
    position: 'absolute',
    top: 0,
    left: '50%',
    transform: 'translate(-50%, -50%)',
    width: '40px',
    height: '40px',
    backgroundColor: theme.palette.primary.main,
    borderRadius: '50%',
    marginBottom: theme.spacing(1.5),
    cursor: 'pointer',
    borderStyle: 'solid',
    borderColor: theme.palette.background.paper,
    borderWidth: theme.spacing(0.5)
  })
);

const AddLineIcon = styled(LineIcon, {
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  fontSize: theme.mixins.pxToRem(16),
  color: '#fff',
  position: 'absolute',
  top: '50%',
  left: '50%',
  transform: 'translate(-50%, -50%)',
  ...(isMobile && {
    fontSize: theme.mixins.pxToRem(12)
  })
}));
const TextAddMobile = styled(Box)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  width: '100%',
  textAlign: 'center',
  color: theme.palette.primary.main
}));

interface Props {
  title: string;
  preventShowAvatar?: boolean;
  canCreate?: boolean;
}

function AddItemCard({
  title: titleProps = 'create_story',
  preventShowAvatar = false,
  canCreate = true
}: Props) {
  const { useSession, i18n, navigate, getSetting } = useGlobal();
  const isMobile = useIsMobile(true);
  const { user } = useSession();

  const { has_live_story } = user || {};

  const hasLiveVideo = has_live_story;

  const viewStoryStatus = getSetting('story.home_page_style');

  const alt = shortenFullName(user?.full_name || user?.title);

  const title = user?.title ?? (user?.full_name || 'NaN');
  const avatar = getImageSrc(user?.avatar, '200x200');

  const style: any = {};

  if (!avatar) {
    style.backgroundColor = colorHash.hex(alt || '');
  }

  const hasStory = user?.can_view_story;

  const handleClick = () => {
    navigate('/story/add');
  };

  const handleClickTitle = () => {
    if (hasStory) {
      navigate(`/story/${user?.id}`);

      return;
    }

    handleClick();
  };

  const iconAddClick = e => {
    e.stopPropagation();
    handleClick();
  };

  if (
    (hasStory || (!hasStory && canCreate)) &&
    !preventShowAvatar &&
    viewStoryStatus === ViewFeedStatus.Avatar
  ) {
    return (
      <RootAvatarStyled
        data-testid={camelCase('storyAddItemCard')}
        onClick={handleClickTitle}
        maxWidth={SIZE_AVATAR_TYPE}
      >
        <WrapperAvatar hasLiveVideo={hasLiveVideo}>
          <UserAvatar
            user={user}
            size={SIZE_AVATAR_TYPE}
            hoverCard={false}
            noStory={!hasStory}
            sx={{ pointerEvents: 'none' }}
            showLiveStream
            showStatus={false}
          />
          {canCreate ? (
            <ButtonAddMobile
              data-testid={camelCase('storyAddIconCard')}
              onClick={iconAddClick}
            >
              <AddLineIcon icon="ico-plus" isMobile={isMobile} />
            </ButtonAddMobile>
          ) : null}
        </WrapperAvatar>
        <TextAddMobile onClick={handleClickTitle}>
          {i18n.formatMessage({ id: titleProps })}
        </TextAddMobile>
      </RootAvatarStyled>
    );
  }

  if (!canCreate) return null;

  return (
    <RootStyled
      data-testid={camelCase('storyAddItemCard')}
      onClick={handleClick}
      isMobile={isMobile}
    >
      {avatar ? (
        <ImageStyled
          src={avatar}
          data-testid={'storyImageAdd'}
          alt={title}
          style={style}
        />
      ) : (
        <ImageBackground data-testid={'storyImageAdd'} style={style}>
          <span>{alt}</span>
        </ImageBackground>
      )}
      <NewStoryText>
        <ButtonAddStyled>
          <AddLineIcon icon="ico-plus" />
        </ButtonAddStyled>
        <Box mt={2}>{i18n.formatMessage({ id: titleProps })}</Box>
      </NewStoryText>
    </RootStyled>
  );
}

export default AddItemCard;

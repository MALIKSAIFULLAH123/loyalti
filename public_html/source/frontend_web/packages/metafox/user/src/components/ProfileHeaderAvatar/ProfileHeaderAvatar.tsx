/**
 * @type: service
 * name: ProfileHeaderAvatar
 */
import { Link, useGlobal } from '@metafox/framework';
import { ProfileHeaderAvatarProps } from '@metafox/user';
import { LineIcon } from '@metafox/ui';
import { colorHash } from '@metafox/utils';
import { Avatar, Box, Button, styled } from '@mui/material';
import * as React from 'react';
import useStyles from './styles';
import PopperMenu from './PopperMenu';
import { isNil } from 'lodash';

const name = 'ProfileHeaderAvatar';

const AvatarWrapper = styled('div', {
  name,
  slot: 'AvatarWrapper',
  shouldForwardProp: props => props !== 'isUpdateAvatar'
})<{
  isUpdateAvatar?: boolean;
}>(({ theme, isUpdateAvatar }) => ({
  marginTop: -96,
  marginRight: theme.spacing(3),
  position: 'relative',
  ...(isUpdateAvatar && {
    marginTop: -200,
    marginRight: 0
  }),
  [theme.breakpoints.down('sm')]: {
    marginTop: -64,
    marginRight: 0,
    marginBottom: theme.spacing(1),
    ...(isUpdateAvatar && {
      marginTop: -100
    })
  }
}));

const EditAvatarButton = styled(Button, { name, slot: 'EditAvatarButton' })(
  ({ theme }) => ({
    textTransform: 'capitalize',
    position: 'absolute',
    top: theme.spacing(1),
    right: theme.spacing(1),
    minWidth: 32,
    height: 32,
    borderRadius: '100% !important',
    [theme.breakpoints.down('sm')]: {
      top: 0,
      right: 0
    }
  })
);

const UserAvatar = styled(Avatar, {
  name,
  slot: 'UserAvatar',
  shouldForwardProp: props =>
    props !== 'hasLiveVideo' &&
    props !== 'isUpdateAvatar' &&
    props !== 'hasNewStory'
})<{
  isUpdateAvatar?: boolean;
  hasLiveVideo?: boolean;
  hasNewStory?: boolean;
}>(({ theme, isUpdateAvatar, hasLiveVideo, hasNewStory }) => ({
  border: '4px solid',
  borderColor: theme.palette.background.paper,
  fontSize: theme.mixins.pxToRem(32),
  '&:before': {
    content: "''",
    position: 'absolute',
    width: '100%',
    height: '100%',
    borderRadius: '50%',
    borderWidth: 'thin',
    borderStyle: 'solid',
    borderColor: theme.palette.border.secondary
  },
  ...(isUpdateAvatar && {
    margin: '0 auto'
  }),
  ...(!hasLiveVideo && {
    [theme.breakpoints.down('sm')]: {
      border: '2px solid',
      borderColor: theme.palette.background.paper
    }
  }),
  ...((hasLiveVideo || !isNil(hasNewStory)) && {
    border: '4px solid !important',
    borderColor: `${
      hasLiveVideo
        ? theme.palette.error.main
        : hasNewStory
        ? theme.palette.primary.main
        : theme.palette.grey[500]
    } !important`,
    '&::before': {
      content: "''",
      position: 'absolute',
      PointerEvent: 'none',
      top: 0,
      left: 0,
      bottom: 0,
      right: 0,
      border: '2.5px solid #fff',
      borderColor: theme.palette.background.paper,
      borderRadius: '100%'
    }
  })
}));

const WrapperUserAvatar = styled(Box, { name, slot: 'WrapperUserAvatar' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center'
  })
);

const WrapperLabel = styled(Box, {
  name,
  slot: 'WrapperLabel'
})(({ theme }) => ({
  marginTop: theme.spacing(-2),
  zIndex: '2',
  display: 'flex'
}));

const AvatarComponent = ({
  avatar,
  alt,
  isUpdateAvatar,
  hasLiveVideo,
  hasNewStory
}) => {
  const { jsxBackend, useTheme } = useGlobal();
  const theme = useTheme();

  const LiveLabel = jsxBackend.get('livestreaming.ui.labelLive');

  const bgColor = colorHash.hex(alt || '');
  const avatarSize = {
    width: { sm: 168, xs: 92 },
    height: { sm: 168, xs: 92 },
    fontSize: { sm: 60, xs: 40 }
  };
  const avatarSizeLarge = {
    width: { sm: 340, xs: 200 },
    height: { sm: 340, xs: 200 }
  };
  const styleLabel = {
    margin: 0,
    height: { sm: '24px', xs: '20px' },
    fontSize: { sm: '12px', xs: '10px' },
    padding: '0 4px',
    borderRadius: 0,
    border: '2px solid #fff',
    borderColor: theme.palette.background.paper
  };

  return (
    <WrapperUserAvatar data-testid="profileUserAvatar">
      <UserAvatar
        component={'span'}
        alt={alt}
        children={alt}
        src={avatar}
        style={{ backgroundColor: bgColor }}
        sx={isUpdateAvatar ? avatarSizeLarge : avatarSize}
        isUpdateAvatar={isUpdateAvatar}
        hasLiveVideo={hasLiveVideo}
        hasNewStory={hasNewStory}
      />
      {hasLiveVideo && LiveLabel ? (
        <WrapperLabel>
          <LiveLabel sx={styleLabel} />
        </WrapperLabel>
      ) : null}
    </WrapperUserAvatar>
  );
};

export default function ProfileHeaderAvatar(props: ProfileHeaderAvatarProps) {
  const {
    alt,
    avatar,
    avatarId,
    canEdit,
    onEdit,
    editLabel,
    editIcon = 'ico-camera',
    isUpdateAvatar = false,
    identity,
    showLiveStream = false
  } = props;
  const classes = useStyles();
  const anchorRef = React.useRef();
  const [openMenu, setOpenMenu] = React.useState(false);

  const { getSetting, useGetItem, getAcl } = useGlobal();
  const appPhotoActive = getSetting('photo');
  const appStoryActive = getSetting('story');
  const viewAcl = getAcl('story.story.view');
  const moderateAcl = getAcl('story.story.moderate');
  const canViewStoryAcl = moderateAcl || viewAcl;
  const item = useGetItem(identity);

  const {
    has_live_story: hasLiveVideoItem = false,
    can_view_story,
    has_new_story
  } = item || {};

  const hasStory = appStoryActive && canViewStoryAcl && can_view_story;

  const hasLiveVideo = hasStory && showLiveStream && hasLiveVideoItem;

  const to = `/photo/${avatarId}`;

  const handleOpenMenu = e => {
    e.preventDefault();

    if (hasStory) {
      setOpenMenu(prev => !prev);
    }
  };

  const linkprops =
    avatarId && !hasStory
      ? {
          to,
          asModal: true
        }
      : {};

  return (
    <AvatarWrapper
      data-testid="profileHeaderAvatar"
      isUpdateAvatar={isUpdateAvatar}
      ref={anchorRef}
    >
      <Link
        {...linkprops}
        underline={'none'}
        onClick={hasStory ? handleOpenMenu : undefined}
        sx={hasStory || avatarId ? { cursor: 'pointer' } : { cursor: 'auto' }}
      >
        <AvatarComponent
          avatar={avatar}
          alt={alt}
          isUpdateAvatar={isUpdateAvatar}
          hasLiveVideo={hasLiveVideo}
          hasNewStory={hasStory ? has_new_story : null}
        />
      </Link>
      {canEdit && appPhotoActive ? (
        <EditAvatarButton
          data-testid="profileHeaderAvatarEdit"
          aria-label={editLabel}
          onClick={onEdit}
          size="small"
          color="default"
        >
          <LineIcon icon={editIcon} className={classes.iconEdit} />
        </EditAvatarButton>
      ) : null}
      {appPhotoActive && hasStory ? (
        <PopperMenu
          appPhotoActive={!!appPhotoActive}
          appStoryActive={!!appStoryActive}
          open={openMenu}
          setOpen={setOpenMenu}
          anchorRef={anchorRef}
          item={item}
          avatarId={avatarId}
        />
      ) : null}
    </AvatarWrapper>
  );
}

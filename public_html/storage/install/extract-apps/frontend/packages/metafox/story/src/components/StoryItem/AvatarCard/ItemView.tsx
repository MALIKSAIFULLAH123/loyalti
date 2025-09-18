import { useGlobal } from '@metafox/framework';
import { SIZE_AVATAR_TYPE } from '@metafox/story/constants';
import { StoryUserProps } from '@metafox/story/types';
import { TruncateText, UserAvatar } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import * as React from 'react';

const name = 'StoryAvatarCard';

const ItemViewStyled = styled(Box, { name, slot: 'itemview' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-start',
  marginRight: theme.spacing(2),
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  position: 'relative',
  '&:hover': {
    cursor: 'pointer'
  },
  '&:last-child': {
    marginRight: 0
  }
}));

const ItemMediaStyled = styled(Box, {
  name,
  slot: 'ItemMediaStyled',
  shouldForwardProp: props => props !== 'hasLiveVideo'
})<{ hasLiveVideo?: boolean }>(({ theme, hasLiveVideo }) => ({
  marginBottom: theme.spacing(1),
  ...(hasLiveVideo && {
    marginBottom: 0
  })
}));
const ItemTitleStyled = styled(Box, { name })(({ theme }) => ({
  width: '100%',
  textAlign: 'center'
}));

export default function StoryAvatarCard({ item }: StoryUserProps) {
  const { navigate, useTheme, useSession } = useGlobal();
  const theme = useTheme();

  const { user } = useSession();

  const isOwner = item?.id === user?.id;

  if (isOwner || !item) return null;

  const { has_live_story } = item || {};

  const hasLiveVideo = has_live_story;

  const handleClick = () => {
    navigate('/story', { state: { related_user_id: item?.id } });
  };

  return (
    <ItemViewStyled
      data-testid="story-main-card"
      onClick={handleClick}
      maxWidth={SIZE_AVATAR_TYPE}
    >
      <ItemMediaStyled hasLiveVideo={hasLiveVideo}>
        <UserAvatar
          user={item}
          size={SIZE_AVATAR_TYPE}
          hoverCard={false}
          sx={{ pointerEvents: 'none' }}
          showLiveStream
        />
      </ItemMediaStyled>
      <ItemTitleStyled>
        <TruncateText
          lines={1}
          variant="body1"
          color="text.primary"
          style={{ fontSize: theme.mixins.pxToRem(13) }}
        >
          {item?.full_name}
        </TruncateText>
      </ItemTitleStyled>
    </ItemViewStyled>
  );
}

StoryAvatarCard.displayName = 'StoryAvatarCard';

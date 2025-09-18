import { useGlobal } from '@metafox/framework';
import { TYPE_LIVE_VIDEO } from '@metafox/story/constants';
import { StoryUserProps, StoryItemProps } from '@metafox/story/types';
import { TruncateText, UserAvatar } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import { camelCase } from 'lodash';
import * as React from 'react';

const name = 'StoryMainCard';

const ItemViewStyled = styled(Box, { name, slot: 'itemview' })(({ theme }) => ({
  flexBasis: '140px',
  flexShrink: 0,
  width: '140px',
  height: '250px',
  marginRight: theme.spacing(1),
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  position: 'relative',
  background: theme.palette.background.paper,
  '&:hover': {
    cursor: 'pointer'
  },
  '&:last-child': {
    marginRight: 0
  },
  '&:before': {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    height: 0,
    boxShadow: '0px 20px 55px 20px rgba(0,0,0,0.5)',
    content: '""',
    zIndex: 2
  },
  '&:after': {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: 0,
    boxShadow: '0px 20px 55px 50px rgba(0,0,0,0.5)',
    content: '""',
    zIndex: 2
  }
}));

const WrapperImage = styled(Box, { name })(({ theme }) => ({
  height: '100%',
  width: '100%'
}));

const ItemMediaStyled = styled(Box, { name })(({ theme }) => ({
  position: 'absolute',
  top: 0,
  padding: theme.spacing(1.5),
  zIndex: 3
}));
const ItemTitleStyled = styled(Box, { name })(({ theme }) => ({
  position: 'absolute',
  bottom: 0,
  padding: theme.spacing(1.25, 1.5),
  width: '100%',
  zIndex: 3
}));

const ImageUrl = styled('div', {
  name: 'PreviewDv',
  slot: 'Root',
  shouldForwardProp: prop => prop !== 'imageUrl'
})<{ imageUrl: string }>(({ theme, imageUrl }) => ({
  display: 'flex',
  alignItems: 'center',
  height: '100%',
  width: '100%',
  borderRadius: theme.shape.borderRadius,
  backgroundImage: `url(${imageUrl})`,
  backgroundRepeat: 'no-repeat',
  backgroundSize: 'cover',
  backgroundPosition: 'center'
}));

export default function StoryItem({ item }: StoryUserProps) {
  const { assetUrl, useGetItems, navigate, useSession, i18n } = useGlobal();

  const stories: StoryItemProps[] = useGetItems(item?.stories);

  const { user } = useSession();

  if (!item) return null;

  const isOwner = item?.id === user?.id;

  const story = stories.find(item => {
    if (item.type === TYPE_LIVE_VIDEO && item?.extra_params?.is_streaming)
      return item;

    if (item.has_seen === false) return item;

    return null;
  });

  const storyActive = story || stories[stories.length - 1];

  const imageUrl = getImageSrc(
    storyActive?.thumbs || storyActive?.image,
    '150',
    assetUrl('story.no_image')
  );

  const handleClick = () => {
    navigate('/story', { state: { related_user_id: item?.id } });
  };

  return (
    <ItemViewStyled
      data-testid={camelCase('story-main-card')}
      onClick={handleClick}
    >
      <WrapperImage>
        <ImageUrl key={imageUrl} imageUrl={imageUrl} />
      </WrapperImage>
      <ItemMediaStyled>
        <UserAvatar
          user={item}
          size={42}
          hoverCard={false}
          sx={{ pointerEvents: 'none' }}
          showLiveStream
        />
      </ItemMediaStyled>
      <ItemTitleStyled data-testid={camelCase('story-main-card-name')}>
        <TruncateText
          lines={2}
          variant="body1"
          style={{ fontWeight: 500, color: '#fff' }}
        >
          {isOwner ? i18n.formatMessage({ id: 'my_story' }) : item?.full_name}
        </TruncateText>
      </ItemTitleStyled>
    </ItemViewStyled>
  );
}

StoryItem.displayName = 'StoryMainCard';

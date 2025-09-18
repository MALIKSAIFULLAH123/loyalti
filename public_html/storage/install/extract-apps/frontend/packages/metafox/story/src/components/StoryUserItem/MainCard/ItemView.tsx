import { useGlobal } from '@metafox/framework';
import { useStoryViewContext } from '@metafox/story/hooks';
import { StoryUserProps } from '@metafox/story/types';
import {
  DotSeparator,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  UserAvatar
} from '@metafox/ui';
import { Typography, styled } from '@mui/material';
import * as React from 'react';
import FromNowStory from '../../FromNowStory';

const name = 'StoryUserItem';

const ItemViewStyled = styled(ItemView, {
  name,
  slot: 'WrapperItem',
  shouldForwardProp: props => props !== 'active'
})<{ active?: boolean }>(({ theme, active }) => ({
  ...(active && {
    backgroundColor: theme.palette.action.hover
  }),
  '&:hover': {
    cursor: 'pointer'
  }
}));

const NewStoryText = styled(Typography, { name, slot: 'text_new_story' })(
  ({ theme }) => ({
    color: theme.palette.primary.main
  })
);

export default function StoryUserItem({
  item,
  wrapAs,
  wrapProps
}: StoryUserProps) {
  const { i18n, useGetItems, useGetItem } = useGlobal();

  const { identityUserStoryActive } = useStoryViewContext();

  const userStoryActive = useGetItem(identityUserStoryActive);

  const stories = useGetItems(item?.stories)?.filter(item => !item.has_seen);

  if (!item) return null;

  return (
    <ItemViewStyled
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid="story-avatar"
      active={item?.id === userStoryActive?.id}
    >
      <ItemMedia>
        <UserAvatar
          user={item}
          size={48}
          hoverCard={false}
          sx={{ pointerEvents: 'none' }}
          showLiveStream
        />
      </ItemMedia>
      <ItemText>
        <ItemTitle lines={1}>{item?.full_name}</ItemTitle>
        {!stories.length && !item?.last_item_timestamp ? null : (
          <DotSeparator sx={{ color: 'text.secondary', mt: 0.5 }}>
            {stories.length ? (
              <NewStoryText component={'span'} variant="body2">
                {i18n.formatMessage(
                  { id: 'total_new_story' },
                  { total: stories.length }
                )}
              </NewStoryText>
            ) : null}
            <FromNowStory value={item?.last_item_timestamp} shorten />
          </DotSeparator>
        )}
      </ItemText>
    </ItemViewStyled>
  );
}

StoryUserItem.displayName = 'StoryAvatarMainCard';

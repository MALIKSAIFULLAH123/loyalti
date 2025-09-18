import { useGlobal } from '@metafox/framework';
import { StoryUserProps } from '@metafox/story/types';
import {
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  UserAvatar,
  UserName
} from '@metafox/ui';
import { Box, styled } from '@mui/material';
import { isEmpty } from 'lodash';
import * as React from 'react';

const name = 'ViewerCard';

const ReactionList = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  width: '100%',
  overflow: 'hidden',
  marginTop: theme.spacing(0.5)
}));

const ImageStyled = styled(Box, {
  name,
  slot: 'ImageStyled'
})(({ theme }) => ({
  marginLeft: theme.spacing(-0.5),
  '&:first-of-type': {
    marginLeft: 0
  }
}));

export default function ViewerCard({
  item,
  wrapAs,
  wrapProps
}: StoryUserProps) {
  const { getSetting, useGetItems } = useGlobal();
  const disableReact = !getSetting('preaction');

  const reactionItems = useGetItems(item?.reactions);

  if (!item) return null;

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="story-viewerCard">
      <ItemMedia>
        <UserAvatar
          user={{ ...item, id: item?.user_id }}
          size={48}
          hoverCard={`/user/${item?.user_id}`}
        />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <UserName
            user={{ ...item, id: item?.user_id }}
            hoverCard={`/user/${item?.user_id}`}
          />
        </ItemTitle>
        {disableReact && isEmpty(reactionItems) ? null : (
          <ReactionList>
            {reactionItems.map((reaction: any) => (
              <ImageStyled key={reaction?.id}>
                <img
                  aria-label={reaction.title}
                  data-testid="itemReaction"
                  src={reaction.src}
                  width="18px"
                  height="18px"
                  alt={reaction.title}
                />
              </ImageStyled>
            ))}
          </ReactionList>
        )}
      </ItemText>
    </ItemView>
  );
}

ViewerCard.displayName = 'ViewerCard';

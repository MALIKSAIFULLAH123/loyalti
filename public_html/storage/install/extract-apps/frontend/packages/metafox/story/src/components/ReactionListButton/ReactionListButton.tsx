import React from 'react';
import { Box, Tooltip, styled } from '@mui/material';
import { useGlobal } from '@metafox/framework';
import { isEmpty } from 'lodash';
import { PauseStatus, StoryItemProps } from '@metafox/story/types';
import { StoryContextProps } from '@metafox/story/context/StoryViewContext';
import { useReactionTemporary } from '@metafox/story/hooks';

const name = 'ReactionThisItem';

const RootStyled = styled(Box, {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'active'
})<{ active?: boolean }>(({ theme, active }) => ({
  display: 'flex',
  flexDirection: 'row',
  '&>img': {
    minWidth: '44px',
    padding: '4px',
    transition: 'transform 180ms',
    transformOrigin: 'center bottom'
  },
  '&>img:hover': { transform: 'scale(1.22)' }
}));

interface Props {
  item: StoryItemProps;
  contextStory: StoryContextProps;
}

const Item = ({
  item,
  classes,
  onClick,
  active
}: {
  classes?: any;
  item: any;
  onClick?: any;
  active?: boolean;
}) => {
  return (
    <RootStyled
      active={active}
      role="button"
      onClick={onClick}
      aria-label={item.title}
      data-testid="itemReaction"
    >
      <Tooltip title={item.title}>
        <img
          role="button"
          aria-label={item.title}
          data-testid="itemReaction"
          src={item.src}
          draggable={false}
          width="44px"
          height="44px"
          alt={item.title}
        />
      </Tooltip>
    </RootStyled>
  );
};

function ReactionListButton({ item: story, contextStory }: Props) {
  const {
    dispatch,
    useIsMobile,
    useReactions = useReactionTemporary
  } = useGlobal();
  const reactions = useReactions();
  const isMobile = useIsMobile();

  const { fire, pauseStatus, reactions: reactionsContext } = contextStory || {};

  if (isEmpty(story)) return null;

  const handleEmojiClick = ({ item }) => {
    fire({ type: 'setReactions', payload: [...reactionsContext, item] });
    dispatch({
      type: 'story/sendReaction',
      payload: {
        identity: story?._identity,
        reaction_id: item?.id
      }
    });
  };

  const handleHover = () => {
    if (isMobile) return;

    if (pauseStatus === PauseStatus.Force) return;

    fire({ type: 'setForcePause', payload: PauseStatus.Pause });
  };

  const handleLeave = () => {
    if (isMobile) return;

    if (pauseStatus === PauseStatus.Force) return;

    fire({ type: 'setForcePause', payload: PauseStatus.No });
  };

  return (
    <RootStyled
      data-testid="reactionListButton"
      onMouseEnter={handleHover}
      onMouseLeave={handleLeave}
    >
      {reactions.map(item => {
        return (
          <Item
            key={item.id.toString()}
            onClick={() => {
              handleEmojiClick({ item });
            }}
            item={item}
          />
        );
      })}
    </RootStyled>
  );
}

export default React.memo(
  ReactionListButton,
  (prev, next) =>
    prev?.item?.id === next?.item?.id &&
    prev?.contextStory?.pauseStatus === next?.contextStory?.pauseStatus &&
    prev?.contextStory?.reactions === next?.contextStory?.reactions
);

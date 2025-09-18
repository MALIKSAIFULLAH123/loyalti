import { useMsgItem } from '@metafox/chat/hooks';
import { useReactionTemporary } from '@metafox/chat/hooks/useReactionChat';
import { useGlobal } from '@metafox/framework';
import { Box, styled, Tooltip } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';

const name = 'ChatReactListPopover';

const ItemRoot = styled(Box, {
  name,
  slot: 'ItemRoot',
  shouldForwardProp: prop => prop !== 'active'
})<{ active: boolean }>(({ theme, active }) => ({
  padding: '4px',
  display: 'flex',
  alignItem: 'center',
  alignSelf: 'center',
  ...(active && {
    background: theme.palette.grey['300'],
    padding: theme.spacing(0.5),
    borderRadius: theme.spacing(1),
    width: 34,
    height: 34,
    display: 'flex',
    alignItem: 'center',
    justifyContent: 'center',
    margin: theme.spacing(0.2)
  })
}));

const ItemImage = styled('img', { name, slot: 'ItemImage' })(({ theme }) => ({
  width: 27,
  height: 27
}));

const Item = ({
  item,
  onClick,
  active
}: {
  item: any;
  onClick?: any;
  active?: boolean;
}) => {
  return (
    <ItemRoot
      active={active}
      role="button"
      onClick={onClick}
      aria-label={item.title}
      data-testid="itemReaction"
    >
      <Tooltip title={item.title}>
        <ItemImage src={item.src} draggable={false} alt={item.title} />
      </Tooltip>
    </ItemRoot>
  );
};

function ReactListPopover({ onEmojiClick, unsetReaction, identity }: any) {
  const { useSession, useReactions = useReactionTemporary } = useGlobal();
  const reactions = useReactions();
  const message = useMsgItem(identity);
  const { user } = useSession();

  const checkReacted = (id: string) => {
    if (isEmpty(message?.reactions?.[id])) return false;

    const result = message?.reactions?.[id].some(
      item => item.user_name === user.user_name
    );

    return result;
  };

  const handleEmojiClick = id => {
    const isReacted = checkReacted(id);

    if (isReacted) {
      unsetReaction(id);
    } else {
      onEmojiClick(id, null);
    }
  };

  return (
    <ItemRoot active={false}>
      {reactions.map(item => {
        return (
          <Item
            key={item.id.toString()}
            onClick={() => {
              handleEmojiClick(`:reaction_${item.id.toString()}:`);
            }}
            item={item}
            active={checkReacted(`:reaction_${item.id}:`)}
          />
        );
      })}
    </ItemRoot>
  );
}

export default ReactListPopover;

import { useReactionChat } from '@metafox/chatplus/hooks';
import { styled } from '@mui/material';
import { isString } from 'lodash';
import React from 'react';

export const emojify2Unicode = (name: string) => name;

const ReactionEmoji = styled('span')(({ theme }) => ({
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  margin: theme.spacing(0, 0.25)
}));

const ImgStyled = styled('img')(({ theme }) => ({
  width: 15,
  height: 15
}));

interface Props {
  id: string;
  usernames: string[];
}

export default function MsgReaction({ id, usernames }: Props) {
  const reactions = useReactionChat();

  if (!id || !usernames) return null;

  const idReaction = isString(id) && id.split(':')[1].split('_')[1];

  if (!idReaction || !reactions) return null;

  const reaction = reactions.find(item => item.id === parseInt(idReaction));

  return (
    <ReactionEmoji>
      <ImgStyled src={reaction?.src} draggable={false} alt={reaction?.title} />
    </ReactionEmoji>
  );
}

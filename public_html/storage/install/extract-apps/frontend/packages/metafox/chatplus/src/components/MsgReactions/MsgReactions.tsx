import { MsgItemShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import MsgReaction from './MsgReaction';

const Reactions = styled('div', {
  shouldForwardProp: props => props !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  display: 'flex',
  margin: 0,
  padding: 0,
  marginBottom: theme.spacing(-1),
  ...(isOwner && {
    flexDirection: 'row-reverse'
  })
}));

const ReactionCount = styled('span')(({ theme }) => ({
  marginLeft: theme.spacing(0.5),
  position: 'relative',
  top: '-1px',
  fontSize: theme.typography.pxToRem(14),
  height: theme.spacing(2.5),
  fontWeight: theme.typography.fontWeightRegular
}));

const ReactionInner = styled('div')(({ theme }) => ({
  cursor: 'pointer',
  color: theme.palette.grey['800'],
  backgroundColor: theme.palette.grey['50'],
  position: 'relative',
  borderRadius: theme.spacing(1),
  lineHeight: theme.spacing(2.5),
  whiteSpace: 'nowrap',
  display: 'inline-flex',
  alignItems: 'center',
  padding: theme.spacing(0.5, 0.75),
  boxShadow: '0px 0px 15px 0px #00000026',
  transform: 'translateY(-10px)',
  height: theme.spacing(3),
  zIndex: 2
}));
interface Props {
  isOwner?: boolean;
  identity: string;
  disabled: boolean;
  reactions: MsgItemShape['reactions'];
}
export default function MsgReactions({
  identity,
  disabled,
  reactions,
  isOwner
}: Props) {
  const { dispatch, getSetting } = useGlobal();

  const disabledReact = !getSetting('preaction');

  const showReactionsList = () => {
    dispatch({ type: 'chatplus/presentReactionsList', payload: { identity } });
  };

  if (disabledReact || disabled || !reactions || !Object.keys(reactions).length)
    return null;

  let total = 0;
  const sortReactions = Object.keys(reactions)
    .sort(
      (a, b) =>
        (reactions[b]?.usernames?.length || 1) -
        (reactions[a]?.usernames?.length || 1) -
        (reactions[b]?.updatedAt?.$date || 1) -
        (reactions[a]?.updatedAt?.$date || 1)
    )
    .reduce((sortTimeReactions, key) => {
      total = total + reactions[key]?.usernames?.length;

      return {
        ...sortTimeReactions,
        [key]: reactions[key]
      };
    }, {});

  return (
    <Reactions isOwner={isOwner}>
      <ReactionInner onClick={showReactionsList}>
        {Object.keys(sortReactions)
          .slice(0, 3)
          .map((k, i) => (
            <MsgReaction id={k} {...sortReactions[k]} key={`k${i}`} />
          ))}
        <ReactionCount>{total}</ReactionCount>
      </ReactionInner>
    </Reactions>
  );
}

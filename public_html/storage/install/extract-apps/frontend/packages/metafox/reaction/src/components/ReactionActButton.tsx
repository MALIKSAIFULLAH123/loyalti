import { useGlobal } from '@metafox/framework';
import { ActButton } from '@metafox/ui';
import React from 'react';
import { ReactionItem } from '../types';

export type ReactionActButtonProps = {
  minimize?: boolean;
  onlyIcon?: boolean;
  identity: string;
  reactedItem?: ReactionItem;
  unreactedItem?: ReactionItem;
  reacted: string;
  label?: string;
};

export default function ReactionActButton({
  identity,
  unreactedItem,
  minimize,
  reactedItem,
  onlyIcon,
  label
}: ReactionActButtonProps) {
  const { popoverBackend, dispatch, useIsMobile } = useGlobal();
  const item = reactedItem ?? unreactedItem;
  const reacted = Boolean(reactedItem);
  const isMobile = useIsMobile(true);
  const onClick = () =>
    dispatch({
      type: 'reactionItem',
      payload: {
        identity,
        reaction_id: item.id,
        is_default: true
      }
    });

  if (!item) return null;

  if (isMobile) {
    return (
      <ActButton
        data-testid="reactionButton"
        aria-label={item.title}
        aria-selected={reacted}
        minimize={minimize}
        data-popover={`/reactions/${identity}`}
        onTouchStart={popoverBackend.onEnterAnchor}
        onMouseDown={popoverBackend.onLeaveAnchor}
        onClick={onClick}
        label={onlyIcon ? undefined : label || item.title}
        src={item.src}
        icon={item.icon}
        color={item.color as any}
      />
    );
  }

  return (
    <ActButton
      data-testid="reactionButton"
      aria-label={item.title}
      aria-selected={reacted}
      minimize={minimize}
      data-popover={`/reactions/${identity}`}
      onMouseEnter={popoverBackend.onEnterAnchor}
      onMouseLeave={popoverBackend.onLeaveAnchor}
      onMouseDown={popoverBackend.onLeaveAnchor}
      onClick={onClick}
      label={onlyIcon ? undefined : label ?? item.title}
      src={item.src}
      icon={item.icon}
      color={item.color as any}
    />
  );
}

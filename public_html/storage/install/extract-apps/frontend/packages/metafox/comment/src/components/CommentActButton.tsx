/**
 * @type: service
 * name: CommentActButton
 */
import { useGlobal } from '@metafox/framework';
import { ActButton, ActButtonProps } from '@metafox/ui';
import React from 'react';

export type CommentActButtonProps = {
  handleAction: any;
  onlyIcon?: boolean;
} & Partial<ActButtonProps>;

export default function CommentActButton({
  minimize,
  onlyIcon,
  handleAction,
  label
}: CommentActButtonProps) {
  const { i18n } = useGlobal();
  const defaultLabel = i18n.formatMessage({ id: 'comment' });

  return (
    <ActButton
      data-testid="commentButton"
      minimize={minimize}
      icon="ico-comment-o"
      onClick={() => handleAction('onPressedCommentActButton')}
      label={onlyIcon ? undefined : label ?? defaultLabel}
    />
  );
}

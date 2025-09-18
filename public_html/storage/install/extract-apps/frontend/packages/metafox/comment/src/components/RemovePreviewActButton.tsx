/**
 * @type: service
 * name: RemovePreviewActButton
 */
import { useGlobal } from '@metafox/framework';
import { ActButton, ActButtonProps } from '@metafox/ui';
import React from 'react';
import { CommentItemActions } from '../types';

export type ReplyActButtonProps = ActButtonProps & {
  actions: CommentItemActions;
  identity: string;
};

export default function RemovePreviewActButton({
  actions,
  identity,
  ...rest
}: ReplyActButtonProps) {
  const { i18n } = useGlobal();

  if (!actions?.removePreviewLink) return null;

  return (
    <ActButton
      data-testid="buttonRemovePreview"
      onClick={actions.removePreviewLink}
      label={i18n.formatMessage({ id: 'remove_preview' })}
      {...rest}
    />
  );
}

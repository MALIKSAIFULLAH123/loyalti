/**
 * @type: service
 * name: ReplyActButton
 */
import { useGlobal } from '@metafox/framework';
import { ActButton, ActButtonProps } from '@metafox/ui';
import React from 'react';

export type ReplyActButtonProps = ActButtonProps & {
  handleAction: any;
  identity: string;
  openReplyComposer: (arg0?: any) => void;
};

export default function ReplyActButton({
  handleAction,
  identity,
  openReplyComposer,
  ...rest
}: ReplyActButtonProps) {
  const { i18n, useGetItem } = useGlobal();
  const comment = useGetItem(identity);
  const ownerComment = useGetItem(comment?.user);

  const onClick = () => {
    if (openReplyComposer)
      openReplyComposer({ replyUser: ownerComment, replyComment: comment });

    handleAction('onPressedReplyActButton');
  };

  return (
    <ActButton
      data-testid="replyButton"
      onClick={onClick}
      label={i18n.formatMessage({ id: 'reply' })}
      {...rest}
    />
  );
}

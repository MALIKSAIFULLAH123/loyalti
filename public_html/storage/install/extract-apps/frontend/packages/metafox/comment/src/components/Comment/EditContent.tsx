import { CommentContentProps } from '@metafox/comment/types';
import Box from '@mui/material/Box';
import React from 'react';
import CommentComposer from '../CommentComposer';
import { useGlobal } from '@metafox/framework';

export default function CommentContent({
  text,
  extra_data,
  identity,
  handleAction,
  isReply,
  parent_user,
  actions
}: CommentContentProps) {
  const { useDialog, getPageParams } = useGlobal();
  const { dialogProps } = useDialog();
  const { _pageType } = getPageParams();

  const onCancel = () => {
    handleAction('comment/editComment/CANCEL');
  };

  const onSuccess = () => {
    handleAction('comment/editComment/SUCCESS');
  };

  return (
    <Box mt={-1}>
      <CommentComposer
        open
        text={text}
        editing
        onCancel={onCancel}
        identity={identity}
        onSuccess={onSuccess}
        extra_data={extra_data}
        isReply={isReply}
        parentUser={parent_user}
        focus={_pageType === 'viewItemInModal' ? dialogProps?.open : true}
        actions={actions}
      />
    </Box>
  );
}

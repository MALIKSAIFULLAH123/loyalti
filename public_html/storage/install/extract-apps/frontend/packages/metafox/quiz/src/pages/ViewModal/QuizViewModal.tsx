/**
 * @type: dialog
 * name: quiz.dialog.quizView
 */

import { connectItem, useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { BlogDetailViewProps } from '@metafox/blog/types';
import { styled } from '@mui/material';
import React from 'react';

const name = 'QuizViewDialog';

const DialogContentStyled = styled(DialogContent, { name, slot: 'root' })(
  ({ theme }) => ({
    padding: '0 !important',
    overflowY: 'visible',
    display: 'flex'
  })
);
const ContentWrapper = styled('div', { name, slot: 'root' })(({ theme }) => ({
  width: '100%',
  maxWidth: '100%'
}));

function QuizViewDialog({ item, identity }: BlogDetailViewProps) {
  const { useDialog, jsxBackend, useIsMobile, i18n } = useGlobal();
  const { dialogProps } = useDialog();
  const DetailView = jsxBackend.get('quiz.block.quizView');
  const isMobile = useIsMobile();

  if (!item) return null;

  return (
    <Dialog
      {...dialogProps}
      maxWidth="md"
      scroll="body"
      data-testid="popupViewQuiz"
    >
      <DialogTitle enableBack={isMobile} disableClose={isMobile}>
        {i18n.formatMessage({ id: 'quiz' })}
      </DialogTitle>
      <DialogContentStyled>
        <ContentWrapper>
          <DetailView item={item} identity={identity} isModalView />
        </ContentWrapper>
      </DialogContentStyled>
    </Dialog>
  );
}

export default connectItem(QuizViewDialog);

/**
 * @type: dialog
 * name: blog.dialog.blogView
 */

import { connectItem, useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { BlogDetailViewProps } from '@metafox/blog/types';
import { styled } from '@mui/material';
import React from 'react';

const name = 'BlogViewDialog';

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

function BlogViewDialog({ item, identity }: BlogDetailViewProps) {
  const { useDialog, jsxBackend, useIsMobile, i18n } = useGlobal();
  const { dialogProps } = useDialog();
  const DetailView = jsxBackend.get('blog.block.blogView');
  const isMobile = useIsMobile();

  if (!item) return null;

  return (
    <Dialog
      {...dialogProps}
      maxWidth="md"
      scroll="body"
      data-testid="popupViewBlog"
    >
      <DialogTitle enableBack={isMobile} disableClose={isMobile}>
        {i18n.formatMessage({ id: 'blog' })}
      </DialogTitle>
      <DialogContentStyled>
        <ContentWrapper>
          <DetailView item={item} identity={identity} isModalView />
        </ContentWrapper>
      </DialogContentStyled>
    </Dialog>
  );
}

export default connectItem(BlogViewDialog);

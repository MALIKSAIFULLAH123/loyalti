/**
 * @type: dialog
 * name: poll.dialog.pollView
 */

import { connectItem, useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import * as React from 'react';
import { PollDetailViewProps } from '../../types';
import useStyles from './styles';

function PollViewDialog({ item, identity }: PollDetailViewProps) {
  const classes = useStyles();
  const { useDialog, jsxBackend, useIsMobile, i18n } = useGlobal();
  const { dialogProps } = useDialog();
  const DetailView = jsxBackend.get('poll.block.pollView');
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
        {i18n.formatMessage({ id: 'poll' })}
      </DialogTitle>
      <DialogContent className={classes.root}>
        <div className={classes.contentWrapper}>
          <DetailView item={item} identity={identity} isModalView />
        </div>
      </DialogContent>
    </Dialog>
  );
}

export default connectItem(PollViewDialog);

/**
 * @type: dialog
 * name: friend.dialog.listTaggedFriend
 */

import { useGlobal, useResourceAction } from '@metafox/framework';
import * as React from 'react';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { ScrollContainer } from '@metafox/layout';
import { compactData } from '@metafox/utils';
import { APP_FEED } from '@metafox/feed/constant';

export default function PresentTaggedFriendsDialog(props) {
  const { i18n, useDialog, ListView, useIsMobile } = useGlobal();
  const isMobile = useIsMobile();

  const { dialogProps } = useDialog();
  const config = useResourceAction(APP_FEED, APP_FEED, 'taggedFriends');

  if (!config) return null;

  const dataSource = {
    apiParams: compactData(props, config.apiParams),
    apiUrl: config.apiUrl
  };

  const gridContainerProps = { spacing: 0 };

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <DialogTitle>{i18n.formatMessage({ id: 'tagged_friends' })}</DialogTitle>
      <DialogContent>
        <ScrollContainer
          autoHide
          autoHeight
          autoHeightMax={isMobile ? '100%' : 300}
        >
          <ListView
            dataSource={dataSource}
            canLoadMore
            gridContainerProps={gridContainerProps}
            gridLayout="Friend - Small List"
            itemLayout="Friend - Small List"
            itemView={'friend.itemView.smallCard'}
            canLoadSmooth
            numberOfItemsPerPage={10}
          />
        </ScrollContainer>
      </DialogContent>
    </Dialog>
  );
}

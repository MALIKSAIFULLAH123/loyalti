/**
 * @type: dialog
 * name: friend.dialog.taggedFriendDialogs
 */

import { useGlobal } from '@metafox/framework';
import * as React from 'react';
import PresentFriendsDialog from './PresentFriendsDialog';

export type PresentTaggedFriendsDialogProps = {
  item_type: string;
  item_id: number;
  excluded_ids?: number[];
};

export default function PresentTaggedFriendsDialog({
  item_type,
  item_id,
  excluded_ids
}: PresentTaggedFriendsDialogProps) {
  const { i18n } = useGlobal();

  return (
    <PresentFriendsDialog
      apiUrl="/feed/tagged-friend"
      apiParams={{ item_type, item_id, excluded_ids, limit: 20 }}
      pagingId={`tagged-friend/${item_type}/${item_id}`}
      dialogTitle={i18n.formatMessage({ id: 'tagged_friends' })}
    />
  );
}

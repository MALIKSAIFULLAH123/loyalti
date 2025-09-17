/**
 * @type: dialog
 * name: friend.dialog.presentMutualFriends
 */

import { useGlobal } from '@metafox/framework';
import * as React from 'react';
import PresentFriendsDialog from './PresentFriendsDialog/PresentFriendsDialog';

export type PresentMutualFriendsDialogProps = {
  user_id: string;
};

export default function PresentMutualFriendsDialog(
  props: PresentMutualFriendsDialogProps
) {
  const { i18n } = useGlobal();

  return (
    <PresentFriendsDialog
      apiUrl="/friend"
      apiParams={{ user_id: props.user_id, view: 'mutual', limit: 20 }}
      pagingId={`mutualFriends/${props.user_id}`}
      dialogTitle={i18n.formatMessage({ id: 'mutual_friends' })}
    />
  );
}

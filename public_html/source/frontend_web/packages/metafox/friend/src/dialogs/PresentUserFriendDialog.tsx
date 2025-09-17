/**
 * @type: dialog
 * name: friend.dialog.presentFriends
 */

import { useGlobal } from '@metafox/framework';
import * as React from 'react';
import PresentFriendsDialog from './PresentFriendsDialog/PresentFriendsDialog';

export type PresentUserFriendsDialogProps = {
  user_id: string;
};

export default function PresentUserFriendsDialog(
  props: PresentUserFriendsDialogProps
) {
  const { i18n } = useGlobal();

  return (
    <PresentFriendsDialog
      apiUrl="/friend"
      apiParams={{ user_id: props.user_id, limit: 20 }}
      pagingId={`friends/${props.user_id}`}
      dialogTitle={i18n.formatMessage({ id: 'Friends' })}
    />
  );
}

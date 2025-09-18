/**
 * @type: dialog
 * name: group.manager.dialog.membershipQuestion
 * chunkName: groupDialog
 */

import { Dialog, useDialog } from '@metafox/dialog';
import React from 'react';
import { RemoteFormBuilder } from '@metafox/form';

const GroupMembershipQuestionDialog = ({
  id,
  dataSource
}: {
  id: string;
  dataSource: any;
}) => {
  const { dialogProps, disableBackdropClick } = useDialog();

  React.useEffect(() => {
    disableBackdropClick(true);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Dialog fullWidth maxWidth="xs" {...dialogProps}>
      <RemoteFormBuilder dialog dataSource={dataSource} />
    </Dialog>
  );
};

export default GroupMembershipQuestionDialog;

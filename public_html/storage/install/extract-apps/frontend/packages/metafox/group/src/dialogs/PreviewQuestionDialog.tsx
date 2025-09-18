/**
 * @type: dialog
 * name: group.dialog.previewQuestionDialog
 */
import { Dialog } from '@metafox/dialog';
import { RemoteFormBuilder } from '@metafox/form';
import { useGlobal, useResourceAction } from '@metafox/framework';
import React from 'react';
import { APP_GROUP } from '../constant';

const PreviewQuestionDialog = ({ id }: { id: string }) => {
  const { useDialog } = useGlobal();
  const { dialogProps } = useDialog();

  const dataSource = useResourceAction(
    APP_GROUP,
    'group_question',
    'getQuestionForm'
  );

  const { open } = dialogProps;

  const dialog = React.useMemo(
    () => (
      <Dialog open={open} maxWidth="md" fullWidth data-testid="popupReport">
        <RemoteFormBuilder dialog dataSource={dataSource} pageParams={{ id }} />
      </Dialog>
    ),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [id, open]
  );

  return dialog;
};

export default PreviewQuestionDialog;

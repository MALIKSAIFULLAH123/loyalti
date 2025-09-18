/**
 * @type: dialog
 * name: group.manager.dialog.rule
 * chunkName: groupDialog
 */

import { useGlobal } from '@metafox/framework';
import { Dialog, useDialog } from '@metafox/dialog';
import React, { useEffect } from 'react';
import { RemoteFormBuilder } from '@metafox/form';

const changeEventName = 'changeRuleGroupDialog';

const GroupRuleDialog = ({
  id,
  dataSource
}: {
  id: string;
  dataSource: any;
}) => {
  const { eventCenter, dispatch } = useGlobal();
  const { dialogProps } = useDialog();

  useEffect(() => {
    const token = eventCenter.on(changeEventName, data =>
      dispatch({ type: changeEventName, payload: data })
    );

    return () => {
      eventCenter.off(changeEventName, token);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Dialog {...dialogProps}>
      <RemoteFormBuilder
        changeEventName={changeEventName}
        dialog
        dataSource={dataSource}
      />
    </Dialog>
  );
};

export default GroupRuleDialog;

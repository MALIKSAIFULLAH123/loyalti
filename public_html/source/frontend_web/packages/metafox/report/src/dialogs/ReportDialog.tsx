/**
 * @type: dialog
 * name: report.dialog.addReport
 */
import { useGlobal } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import { RemoteFormBuilder } from '@metafox/form';
import qs from 'querystring';
import React from 'react';

const url = 'item_id=:item_id&item_type=:item_type&report_owner=:report_owner';

export default function ReportDialog(props) {
  const { useDialog, compactData } = useGlobal();
  const { dialogProps } = useDialog();

  const data = compactData(url, props);

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth data-testid="popupReport">
      <RemoteFormBuilder
        dialog
        keepPaginationData
        dataSource={{
          apiUrl: `/report/form?${qs.stringify(data)}`
        }}
      />
    </Dialog>
  );
}

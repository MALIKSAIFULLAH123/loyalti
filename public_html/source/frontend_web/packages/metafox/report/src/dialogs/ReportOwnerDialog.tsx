/**
 * @type: dialog
 * name: report.dialog.addReportOwner
 */
import { useGlobal } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import { RemoteFormBuilder } from '@metafox/form';
import qs from 'querystring';
import React from 'react';

const url = 'item_id=:item_id&item_type=:item_type';

export default function ReportOwnerDialog(props) {
  const { useDialog, compactData } = useGlobal();
  const { dialogProps } = useDialog();

  const data = compactData(url, props);

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth data-testid="popupReport">
      <RemoteFormBuilder
        keepPaginationData
        dialog
        dataSource={{
          apiUrl: `/report-owner/form?${qs.stringify(data)}`
        }}
      />
    </Dialog>
  );
}

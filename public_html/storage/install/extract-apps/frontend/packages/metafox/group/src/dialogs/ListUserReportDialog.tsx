/**
 * @type: dialog
 * name: group.dialog.listReportDialog
 */

import { useGlobal, useResourceAction } from '@metafox/framework';
import { APP_REPORT, REPORT_OWNER } from '@metafox/report';
import * as React from 'react';
import ListReportDialog from './ReportDialog/ListReportDialog';

export type ListReportProps = {
  id: string;
};

export default function PresentMutualFriendsDialog({ id }: ListReportProps) {
  const { i18n, compactUrl } = useGlobal();
  const config = useResourceAction(
    APP_REPORT,
    REPORT_OWNER,
    'listUsersReportOwner'
  );

  return (
    <ListReportDialog
      apiUrl={compactUrl(config.apiUrl, { id })}
      pagingId={`listReport/${id}`}
      apiParams={{}}
      dialogTitle={i18n.formatMessage({ id: 'people_who_reported_post' })}
    />
  );
}

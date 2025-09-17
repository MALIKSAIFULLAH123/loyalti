/**
 * @type: ui
 * name: ui.dataGrid.error
 */

import React from 'react';
import ErrorPage from '@metafox/core/pages/ErrorPage/Page';

export default function LoadingComponent({
  error
}: {
  error?: Record<string, any>;
}) {
  return <ErrorPage error={error} />;
}

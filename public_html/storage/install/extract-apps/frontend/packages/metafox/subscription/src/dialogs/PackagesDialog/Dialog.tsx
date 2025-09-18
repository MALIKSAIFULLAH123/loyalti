/**
 * @type: dialog
 * name: subscription.dialog.PackagesDialog
 */

import { useGlobal, Link } from '@metafox/framework';
import { Dialog, DialogTitle } from '@metafox/dialog';
import React from 'react';
import { Box } from '@mui/material';

export default function PackagesDialog({
  dataSource,
  title,
  hasComparison,
  meta,
  relatedFieldName
}) {
  const { useDialog, ListView, i18n, layoutBackend } = useGlobal();
  const { dialogProps } = useDialog();

  const itemView = 'subscription_package.itemView.smallCard';
  const to = '/subscription/package';
  const gridLayout = 'Subscription - Dialog - List';
  const styleProps = layoutBackend.getItemPreset(gridLayout);

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <DialogTitle
        children={title ?? i18n.formatMessage({ id: 'subscription_packages' })}
        data-testid="popupTitle"
      />
      <Box sx={{ flex: 1, minHeight: 0, maxHeight: '100%', overflow: 'auto' }}>
        <ListView
          dataSource={dataSource}
          itemView={itemView}
          limitItemsLoadSmooth={3}
          gridLayout={gridLayout}
          itemLayout={gridLayout}
          itemProps={{
            ...styleProps?.itemProps,
            setFieldValue: meta?.setFieldValue,
            relatedFieldName
          }}
        />
        {hasComparison ? (
          <Box
            px={2}
            pb={2}
            sx={{ display: 'flex', justifyContent: 'flex-end' }}
          >
            <Link to={to} color="primary" target="_blank">
              {i18n.formatMessage({ id: 'compare_packages' })}
            </Link>
          </Box>
        ) : null}
      </Box>
    </Dialog>
  );
}

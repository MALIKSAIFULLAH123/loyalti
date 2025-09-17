/**
 * @type: dialog
 * name: layout.dialog.ManageNoContentLayout
 * chunkName: layoutEditor
 */
import { useGlobal } from '@metafox/framework';
import React from 'react';
import ManageItemDialog from './ManageItemDialog';

export default function ManageNoContentLayout() {
  const { layoutBackend, dispatch, i18n } = useGlobal();
  const allItems = Object.keys(layoutBackend.getNoContentPresets()).sort();

  const handleEdit = (styleName: string) => {
    dispatch({
      type: '@layout/editNoContentLayout',
      payload: { styleName }
    });
  };

  return (
    <ManageItemDialog
      allItems={allItems}
      title={i18n.formatMessage({ id: 'layout_manage_no_contents_presets' })}
      onEdit={handleEdit}
    />
  );
}

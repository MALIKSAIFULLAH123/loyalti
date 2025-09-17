/**
 * @type: dialog
 * name: layout.dialog.ManageItemLayout
 * chunkName: layoutEditor
 */
import { useGlobal } from '@metafox/framework';
import React from 'react';
import ManageItemDialog from './ManageItemDialog';

export default function ManageItemLayout() {
  const { layoutBackend, dispatch, i18n } = useGlobal();
  const allItems = Object.keys(layoutBackend.getItemPresets()).sort();

  const handleEdit = (styleName: string) => {
    dispatch({
      type: '@layout/editItemLayout',
      payload: { styleName }
    });
  };

  return (
    <ManageItemDialog
      allItems={allItems}
      title={i18n.formatMessage({ id: 'layout_manage_item_presets' })}
      onEdit={handleEdit}
    />
  );
}

/**
 * @type: dialog
 * name: layout.dialog.ManageBlockLayout
 * chunkName: layoutEditor
 */
import { useGlobal } from '@metafox/framework';
import React from 'react';
import ManageItemDialog from './ManageItemDialog';

export default function ManageBlockLayout() {
  const { layoutBackend, dispatch, i18n } = useGlobal();
  const allItems = Object.keys(layoutBackend.getBlockPresets()).sort();

  const handleEdit = (styleName: string) => {
    dispatch({
      type: '@layout/editBlockLayout',
      payload: { styleName }
    });
  };

  return (
    <ManageItemDialog
      allItems={allItems}
      title={i18n.formatMessage({ id: 'layout_manage_block_presets' })}
      onEdit={handleEdit}
    />
  );
}

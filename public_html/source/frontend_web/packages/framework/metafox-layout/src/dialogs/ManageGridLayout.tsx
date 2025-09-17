/**
 * @type: dialog
 * name: layout.dialog.ManageGridLayout
 * chunkName: layoutEditor
 */
import { useGlobal } from '@metafox/framework';
import React from 'react';
import ManageItemDialog from './ManageItemDialog';

export default function ManageGridLayout() {
  const { layoutBackend, dispatch, i18n } = useGlobal();
  const allItems = Object.keys(layoutBackend.getGridPresets()).sort();

  const handleEdit = (styleName: string) => {
    dispatch({
      type: '@layout/editGridLayout',
      payload: { styleName }
    });
  };

  return (
    <ManageItemDialog
      allItems={allItems}
      title={i18n.formatMessage({ id: 'layout_manage_grid_presets' })}
      onEdit={handleEdit}
    />
  );
}

/**
 * @type: ui
 * name: layout.popup.ManagePresetPopup
 * chunkName: layoutEditor
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  Box,
  List,
  ListItemButton,
  ListItemIcon,
  ListItemSecondaryAction,
  ListItemText
} from '@mui/material';
import React from 'react';

const items = [
  {
    name: 'manageBlockLayout',
    label: 'layout_manage_block_presets',
    icon: 'ico-list-o',
    value: '@layout/manageBlockLayout',
    showWhen: ['falsy', 'previewDevice']
  },
  {
    name: 'manageGridLayout',
    label: 'layout_manage_grid_presets',
    icon: 'ico-list-o',
    value: '@layout/manageGridLayout',
    showWhen: ['falsy', 'previewDevice']
  },
  {
    name: 'manageItemLayout',
    label: 'layout_manage_item_presets',
    icon: 'ico-list-o',
    value: '@layout/manageItemLayout',
    showWhen: ['falsy', 'previewDevice']
  },
  {
    name: 'manageNoContentLayout',
    label: 'layout_manage_no_contents_presets',
    icon: 'ico-list-o',
    value: '@layout/manageNoContentLayout',
    showWhen: ['falsy', 'previewDevice']
  }
];

export default function ManagePresetPopup() {
  const { usePopover, dispatch, i18n } = useGlobal();
  const popover = usePopover();

  const handleClick = (value: string) => {
    popover.closePopover();
    dispatch({ type: value });
  };

  return (
    <Box sx={{ width: 220 }}>
      <List>
        {items.map(item => (
          <ListItemButton
            dense
            disableRipple
            disableTouchRipple
            onClick={() => handleClick(item.value)}
            key={item.value.toString()}
          >
            <ListItemIcon>
              <LineIcon icon={item.icon} />
            </ListItemIcon>
            <ListItemText primary={i18n.formatMessage({ id: item.label })} />
            <ListItemSecondaryAction>...</ListItemSecondaryAction>
          </ListItemButton>
        ))}
      </List>
    </Box>
  );
}

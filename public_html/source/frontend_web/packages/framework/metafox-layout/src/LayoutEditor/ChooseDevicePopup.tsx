/**
 * @type: ui
 * name: layout.popup.ChooseDevicePopup
 * chunkName: layoutEditor
 */

import { useGlobal } from '@metafox/framework';
import {
  Box,
  List,
  ListItem,
  ListItemButton,
  ListItemSecondaryAction,
  ListItemText
} from '@mui/material';
import React from 'react';
import devices from '../DeviceList';

export default function ChooseDevicePopup() {
  const { usePopover, dispatch, i18n } = useGlobal();
  const popover = usePopover();

  const previewOnDevice = (value: string) => {
    popover.closePopover();
    dispatch({ type: '@layout/previewOnDevice', payload: value });
  };

  return (
    <Box sx={{ width: 220 }}>
      <List>
        <ListItem>
          <ListItemText sx={{ fontSize: 12, color: 'text.secondary' }}>
            {i18n.formatMessage({ id: 'choose_a_device' })}
          </ListItemText>
        </ListItem>
        {devices.map(item => (
          <ListItemButton
            dense
            disableRipple
            disableTouchRipple
            onClick={() => previewOnDevice(item.value)}
            key={item.value.toString()}
          >
            <ListItemText primary={item.label} />
            <ListItemSecondaryAction>{item.size}</ListItemSecondaryAction>
          </ListItemButton>
        ))}
      </List>
    </Box>
  );
}

/**
 * @type: ui
 * name: layout.popup.ChoosePageSizePopup
 * chunkName: layoutEditor
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  Box,
  List,
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText
} from '@mui/material';
import React from 'react';
import { PageSize } from '../types';

const map = {
  large: 'large',
  medium: 'medium: screen < 1281 px',
  sMedium: 'medium: screen < 1024 px',
  small: 'small: screen < 768 px'
};

export default function ChoosePageSizePopup({ pageSize, pageName }) {
  const { layoutBackend, usePopover, i18n } = useGlobal();
  const popover = usePopover();
  const [sizes] = React.useState<PageSize[]>(
    layoutBackend.getExistingSizes(pageName)
  );
  const existingOptions = sizes.map(x => ({
    label: map[x],
    value: x
  }));

  const enterEditPageSize = (size: PageSize) => {
    layoutBackend.enterEditPageSize(pageName, size);
    popover.closePopover();
  };

  return (
    <Box sx={{ width: 220 }}>
      <List>
        <ListItem>
          <ListItemText sx={{ fontSize: 12, color: 'text.secondary' }}>
            {i18n.formatMessage({ id: 'choose_edit_size' })}
          </ListItemText>
        </ListItem>
        {existingOptions.map(item => (
          <ListItemButton
            dense
            onClick={() => enterEditPageSize(item.value)}
            selected={pageSize === item.value}
            key={item.value.toString()}
          >
            <ListItemIcon>
              <LineIcon icon={'ico-mobile-o'} />
            </ListItemIcon>
            <ListItemText primary={i18n.formatMessage({ id: item.label })} />
          </ListItemButton>
        ))}
      </List>
    </Box>
  );
}

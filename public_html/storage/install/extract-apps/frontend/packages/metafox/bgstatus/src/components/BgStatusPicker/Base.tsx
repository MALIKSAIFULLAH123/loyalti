import { useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { ScrollContainer } from '@metafox/layout';
import { Box, useMediaQuery } from '@mui/material';
import { useTheme } from '@mui/material/styles';
import React from 'react';
import ListView from './ListView';

export interface BgStatusPickerProps {
  onSelectItem: (item: unknown) => void;
  selectedId?: number;
}

export default function BgStatusPicker(props: BgStatusPickerProps) {
  const { useDialog, i18n } = useGlobal();
  const { dialogProps, setDialogValue, closeDialog } = useDialog();
  const { onSelectItem, selectedId } = props;
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));

  const handleSelect = (item: unknown) => {
    if (onSelectItem) {
      onSelectItem(item);
    }

    setDialogValue(item);
    closeDialog();
  };

  const scrollProps = isSmallScreen ? { autoHeightMax: 'none' } : {};

  return (
    <Dialog maxWidth="sm" fullWidth {...dialogProps}>
      <DialogTitle enableBack disableClose>
        {i18n.formatMessage({ id: 'select_background_status' })}
      </DialogTitle>
      <DialogContent variant="fitScroll">
        <ScrollContainer {...scrollProps}>
          <Box sx={{ p: 2 }}>
            <ListView handleSelect={handleSelect} selectedId={selectedId} />
          </Box>
        </ScrollContainer>
      </DialogContent>
    </Dialog>
  );
}

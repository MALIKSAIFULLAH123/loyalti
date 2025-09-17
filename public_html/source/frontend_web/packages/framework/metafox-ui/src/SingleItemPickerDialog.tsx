/**
 * @type: dialog
 * name: friend.dialog.MultipleFriendPicker
 */
import { SingleItemPickerDialogProps, useGlobal } from '@metafox/framework';
import { DialogContent, DialogTitle } from '@metafox/dialog';
import { BlockContext, ScrollContainer } from '@metafox/layout';
import { DialogSearchInput } from '@metafox/ui';
import { Box, Dialog, Grid, useMediaQuery } from '@mui/material';
import { useTheme } from '@mui/material/styles';
import { range } from 'lodash';
import React from 'react';

export default function SingleItemPickerDialog({
  onChanged,
  items,
  selectedItem,
  onSelectItem,
  placeholder,
  dialogTitle,
  loading,
  searchInput: SearchInput = DialogSearchInput,
  fullWidth = true,
  maxWidth = 'sm',
  emptyPage = 'core.block.no_content',
  emptyPageProps,
  'data-testid': testid,
  itemView,
  gridLayout,
  itemLayout
}: SingleItemPickerDialogProps) {
  const { useDialog, layoutBackend, jsxBackend } = useGlobal();
  const { dialogProps, setDialogValue } = useDialog();
  const EmptyPage = jsxBackend.get(emptyPage);
  const styleProps = layoutBackend.normalizeDisplayingPresets({
    gridLayout,
    itemLayout
  });

  const ResultItemControl = jsxBackend.get(itemView);

  const LoadingSkeleton = jsxBackend.get(`${itemView}.skeleton`);
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));
  const scrollProps = isSmallScreen ? { autoHeightMax: 'none' } : {};

  const onSubmit = React.useCallback(
    (item: unknown) => {
      onSelectItem(item);
      setDialogValue(item);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [onSelectItem]
  );

  return (
    <BlockContext.Provider value={styleProps}>
      <Dialog
        {...dialogProps}
        maxWidth={maxWidth}
        fullWidth={fullWidth}
        data-testid={testid}
      >
        <DialogTitle>{dialogTitle}</DialogTitle>
        <DialogContent variant="fitScroll">
          <Box sx={{ p: 2 }}>
            <SearchInput
              autoFocus
              placeholder={placeholder}
              onChanged={onChanged}
            />
          </Box>
          <ScrollContainer {...scrollProps}>
            {loading && LoadingSkeleton
              ? range(0, 4).map(x => (
                  <LoadingSkeleton wrapAs={Grid} key={x.toString()} />
                ))
              : null}
            {!loading && items?.length > 0
              ? items.map(item => (
                  <ResultItemControl
                    selected={item.id === selectedItem?.id}
                    onClick={() => onSubmit(item)}
                    item={item}
                    key={item.id.toString()}
                    wrapAs={Grid}
                  />
                ))
              : null}
            {!loading && !items.length ? (
              <Box sx={{ p: 3 }}>
                <EmptyPage {...emptyPageProps} />
              </Box>
            ) : null}
          </ScrollContainer>
        </DialogContent>
      </Dialog>
    </BlockContext.Provider>
  );
}

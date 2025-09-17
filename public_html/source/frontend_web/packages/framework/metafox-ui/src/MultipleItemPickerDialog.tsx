import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { MultipleItemPickerDialogProps, useGlobal } from '@metafox/framework';
import { BlockContext, ScrollContainer } from '@metafox/layout';
import { ChipArray, DialogSearchInput, LineIcon } from '@metafox/ui';
import { Box, Chip, Grid, useMediaQuery } from '@mui/material';
import { styled, useTheme } from '@mui/material/styles';
import { get, range, some, isEqual, isEmpty } from 'lodash';
import React from 'react';

const RemoveIcon = styled(LineIcon, {
  name: 'removeIcon',
  slot: 'icon'
})(({ theme }) => ({
  fontSize: `${theme.mixins.pxToRem(13)} !important`
}));

const ContentWraper = styled('div', {
  shouldForwardProp: props => props !== 'height'
})<{ height?: string }>(({ theme, height }) => ({
  ...(height && {
    height,
    maxHeight: height
  })
}));

export default function MultipleItemPickerDialog({
  onChanged,
  items,
  value,
  onSelectItem,
  selectedItems,
  placeholder,
  fullWidth = true,
  dialogTitle,
  loading,
  itemView,
  chipControl: ChipControl = Chip,
  searchInput: SearchInput = DialogSearchInput,
  maxWidth = 'sm',
  emptyPage = 'core.block.no_content',
  emptyPageProps,
  gridLayout,
  itemLayout,
  leftButton = true,
  chipLabel = 'full_name',
  heightContent = '100%',
  forceUpdateValueOnClose = false
}: MultipleItemPickerDialogProps) {
  const { useDialog, jsxBackend, layoutBackend, i18n, dialogBackend } =
    useGlobal();
  const { dialogProps, closeDialog, setDialogValue, setDialogResolveValue } =
    useDialog();
  const gridConfig = layoutBackend.getGridPreset(gridLayout);
  const itemConfig = layoutBackend.getItemPreset(itemLayout);

  const { gridItemProps } = gridConfig;
  const { itemProps } = itemConfig || {};
  const ResultItemControl = jsxBackend.get(itemView);
  const LoadingSkeleton = jsxBackend.get(`${itemView}.skeleton`);

  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));

  const EmptyPage = jsxBackend.get(emptyPage);

  const onSubmit = () => {
    setDialogValue(selectedItems);
    closeDialog();
  };

  const filterItems = items.filter(item =>
    selectedItems.every(selectedItem => selectedItem.id !== item.id)
  );

  const scrollProps = isSmallScreen ? { autoHeightMax: 'none' } : {};

  const onHandleClose = forceUpdateValueOnClose ? onSubmit : closeDialog;

  const onBackClick = async () => {
    if (!isEmpty(selectedItems) && !isEqual(selectedItems, value)) {
      const ok = await dialogBackend.confirm({
        message: i18n.formatMessage({
          id: 'the_change_you_made_will_not_be_saved'
        }),
        title: i18n.formatMessage({
          id: 'unsaved_changes'
        })
      });

      if (!ok) {
        return;
      }
    }

    setDialogResolveValue(value);
    closeDialog();
  };

  return (
    <BlockContext.Provider value={{ itemProps }}>
      <Dialog
        maxWidth={maxWidth}
        fullWidth={fullWidth}
        {...dialogProps}
        onClose={onHandleClose}
      >
        <DialogTitle
          onBackClick={onBackClick}
          onDoneClick={onSubmit}
          enableBack
          enableDone
          disableClose
        >
          {dialogTitle}
        </DialogTitle>
        <DialogContent variant="fitScroll">
          <Box sx={{ pt: 2, pb: 1, px: 2 }}>
            <SearchInput
              autoFocus
              placeholder={i18n.formatMessage({ id: placeholder })}
              onChanged={onChanged}
              sx={{ borderWidth: theme.palette.mode === 'light' ? 1 : 0 }}
            />
          </Box>
          {selectedItems.length ? (
            <ChipArray sx={{ px: 2, pb: 2 }}>
              {selectedItems.map(item => (
                <ChipControl
                  variant="outlined"
                  onDelete={() => onSelectItem(item)}
                  label={get(item, chipLabel)}
                  key={item.id.toString()}
                  deleteIcon={<RemoveIcon icon="ico-close" />}
                />
              ))}
            </ChipArray>
          ) : null}
          <ScrollContainer {...scrollProps}>
            <ContentWraper height={heightContent}>
              {loading && LoadingSkeleton
                ? range(0, 4).map(x => (
                    <LoadingSkeleton
                      wrapAs={Grid}
                      wrapProps={gridItemProps}
                      key={x.toString()}
                    />
                  ))
                : null}
              {!loading && filterItems.length
                ? filterItems.map(item => (
                    <ResultItemControl
                      wrapAs={Grid}
                      wrapProps={gridItemProps}
                      selected={some(selectedItems, x => x.id === item.id)}
                      onClick={() => onSelectItem(item)}
                      item={item}
                      key={item.id.toString()}
                    />
                  ))
                : null}
              {!loading && !filterItems.length ? (
                <Box sx={{ p: 2 }}>
                  <EmptyPage {...emptyPageProps} />
                </Box>
              ) : null}
            </ContentWraper>
          </ScrollContainer>
        </DialogContent>
      </Dialog>
    </BlockContext.Provider>
  );
}

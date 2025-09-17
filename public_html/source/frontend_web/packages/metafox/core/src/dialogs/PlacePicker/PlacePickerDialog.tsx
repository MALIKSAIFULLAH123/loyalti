/**
 * @type: dialog
 * name: core.dialog.PlacePickerDialog
 */
import { Address, useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { DialogSearchInput } from '@metafox/ui';
import { assign } from 'lodash';
import React from 'react';
import ResultItem from './ResultItem';
import useStyles from './styles';
import { Box, CircularProgress } from '@mui/material';
import { ScrollContainer } from '@metafox/layout';
import useSearchPlaces from './useSearchPlaces';
import { compareSameLocation } from './utils';

export type ClassesKeys =
  | 'searchIcon'
  | 'dialogContent'
  | 'dialogTitle'
  | 'mapStage'
  | 'dialog'
  | 'inputStage';

export type PlacePickerProps = {
  classes: Record<ClassesKeys, string>;
  inputControl: React.ElementType<any>;
  title: string;
  placeholder: string;
  defaultValue: Address;
};

const assignAddress = (x, y) => {
  if (!x) return {};

  // hotfix API current not store address full
  if (x?.name && x?.address && x.name !== x.address) return x;

  return assign({}, x, y);
};

function PlacePickerDialog({ defaultValue }: PlacePickerProps) {
  const classes = useStyles();
  const { useDialog, i18n } = useGlobal();
  const { dialogProps, setDialogValue } = useDialog();

  const title = i18n.formatMessage({ id: 'search_for_location' });
  const placeholder = i18n.formatMessage({ id: 'search_dots' });
  const mapRef = React.useRef<HTMLDivElement>();
  const [items, query, handleSearch, currentItemState, { loading }] =
    useSearchPlaces(mapRef, {
      ...defaultValue
    });

  const currentItem = assignAddress(defaultValue, currentItemState);

  const onChanged = React.useCallback(
    (value: string) => handleSearch(value),
    [handleSearch]
  );

  const handleSelect = (item: unknown) => {
    setDialogValue({ ...(item as any), query, show_map: true });
  };

  const handleReset = () => {
    setDialogValue(false);
  };

  return (
    <Dialog
      className={classes.dialog}
      maxWidth="sm"
      fullWidth
      data-testid="dialogLocationPicker"
      {...dialogProps}
    >
      <DialogTitle data-testid="popupTitle" enableBack disableClose>
        {title}
      </DialogTitle>
      <div className={classes.inputStage}>
        <DialogSearchInput
          autoFocus
          data-testid="searchBox"
          placeholder={placeholder}
          onChanged={onChanged}
        />
      </div>
      <DialogContent className={classes.dialogContent}>
        <ScrollContainer autoHide autoHeight autoHeightMax="100%">
          <div className={classes.dialogContentTitle}>
            {i18n.formatMessage({ id: 'base_on_your_location' })}
          </div>
          <div className={classes.mapStage} ref={mapRef} />
          {currentItem?.full_address && !query && (
            <ResultItem
              active
              name={currentItem.name || currentItem.address}
              address={currentItem.full_address}
              onClick={handleReset}
            />
          )}
          {loading ? (
            <Box
              p={2}
              sx={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                width: '100%'
              }}
            >
              <CircularProgress size={24} />
            </Box>
          ) : (
            <Box>
              {!!items?.length &&
                items.map((item, index) => {
                  if (
                    compareSameLocation(item, currentItem) ||
                    // conflict nearBySearch and textSearch, on some location GoogleAPI response lat lng have a bit different
                    (compareSameLocation(item, currentItem, 1) &&
                      item.name === currentItem.name &&
                      currentItem.address?.includes(item.address))
                  )
                    return null;

                  return (
                    <ResultItem
                      name={item.name}
                      address={item.full_address || item.address}
                      key={index.toString()}
                      onClick={() => handleSelect(item)}
                    />
                  );
                })}
            </Box>
          )}
        </ScrollContainer>
      </DialogContent>
    </Dialog>
  );
}

export default PlacePickerDialog;

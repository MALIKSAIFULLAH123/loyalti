import { useGlobal } from '@metafox/framework';
import { styled, Tooltip } from '@mui/material';
import { isFunction } from 'lodash';
import React from 'react';
import { BgStatusItemShape, BgStatusListBaseProps } from '../../types';

export type BgStatusListProps = BgStatusListBaseProps;

const name = 'AttachBackgroundStatus';

const size = 24;

const Wrapper = styled('div', { name, slot: 'root' })(({ theme }) => ({
  display: 'flex',
  flexWrap: 'wrap'
}));

const ItemRoot = styled('div', { name, slot: 'root' })(({ theme }) => ({
  cursor: 'pointer',
  display: 'inline-flex',
  padding: theme.spacing(0.5)
}));

const SelectBackgroundButton = styled('div', { name, slot: 'root' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
  })
);

const SelectBackgroundIcon = styled('div', { name, slot: 'root' })(
  ({ theme }) => ({
    width: size,
    height: size,
    borderRadius: theme.shape.borderRadius / 2,
    border: 0,
    backgroundImage: 'linear-gradient(to top, #7ed290, #076e1e);'
  })
);

const RemoveBackgroundIcon = styled('div', { name, slot: 'root' })(
  ({ theme }) => ({
    width: size,
    height: size,
    borderRadius: theme.shape.borderRadius / 2,
    border: '1px solid rgba(0,0,0,0.2)',
    backgroundColor: theme.mixins.backgroundColor('paper')
  })
);

export default function AttachBackgroundStatus(props: BgStatusListProps) {
  const { onClear, onSelectItem } = props;

  const [selectedId, setSelectedId] = React.useState(props.selectedId);
  const { i18n, dialogBackend } = useGlobal();

  const handleSelect = React.useCallback(
    (item: BgStatusItemShape) => {
      setSelectedId(item.id);
      isFunction(onSelectItem) && onSelectItem(item);
    },
    [onSelectItem]
  );

  const handleClear = React.useCallback(() => {
    setSelectedId(0);

    isFunction(onClear) && onClear();
  }, [onClear]);

  const openPicker = () => {
    dialogBackend.present({
      component: 'bgstatus.dialog.BgStatusPicker',
      props: {
        onSelectItem: handleSelect,
        onClear: handleClear,
        selectedId
      }
    });
  };

  return (
    <Wrapper>
      <Tooltip title={i18n.formatMessage({ id: 'select_background_status' })}>
        <ItemRoot>
          <SelectBackgroundButton
            aria-label="select_background_status"
            data-testid="selectBackgroundStatusButton"
            onClick={openPicker}
            role="button"
          >
            <SelectBackgroundIcon />
          </SelectBackgroundButton>
        </ItemRoot>
      </Tooltip>
      {selectedId > 0 && (
        <Tooltip title={i18n.formatMessage({ id: 'remove_background_status' })}>
          <ItemRoot>
            <SelectBackgroundButton
              data-testid="removeBackgroundStatusButton"
              aria-label="remove_background_status"
              onClick={handleClear}
              role="button"
            >
              <RemoveBackgroundIcon />
            </SelectBackgroundButton>
          </ItemRoot>
        </Tooltip>
      )}
    </Wrapper>
  );
}

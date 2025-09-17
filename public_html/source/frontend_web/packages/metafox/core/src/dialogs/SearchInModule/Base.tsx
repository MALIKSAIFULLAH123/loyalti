/**
 * @type: dialog
 * name: core.dialog.searchInModule
 */
import { useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { LineIcon } from '@metafox/ui';
import { InputBase, styled } from '@mui/material';
import clsx from 'clsx';
import React, { useState } from 'react';
import useStyles from './styles';
import qs from 'query-string';
interface Props {
  title: string;
  placeholder: string;
  item: Record<string, any>;
  params?: Record<string, any>;
}

const InputBaseWrapper = styled(InputBase)(({ theme }) => ({
  '& input': {
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap'
  }
}));

export default function SearchInModuleDialog({
  title,
  placeholder,
  item,
  params
}: Props) {
  const { useDialog, navigate } = useGlobal();
  const { dialogProps, closeDialog } = useDialog();
  const classes = useStyles();

  const [isFocus, setFocus] = useState(false);
  const [value, setValue] = useState('');

  const handleFocus = () => {
    setFocus(true);
  };

  const handleBlur = () => {
    setFocus(false);
  };

  const handChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = e.target;
    setValue(value);
  };

  const handleKeyDown = (evt: React.KeyboardEvent) => {
    const code = evt?.keyCode;

    if (code !== 13) return; // enter

    evt.preventDefault();
    handleSubmit();
  };

  const handleSubmit = () => {
    const trimmedValue = value.trim();

    if (!trimmedValue) return;

    const { id, module_name } = item;

    navigate({
      pathname: `/${module_name}/search/${id}`,
      search: qs.stringify({
        q: trimmedValue,
        ...params
      })
    });

    closeDialog();
  };

  return (
    <Dialog
      {...dialogProps}
      maxWidth="sm"
      fullWidth
      fullScreen={false}
      data-testid="dialogSearchInApp"
    >
      <DialogTitle>
        <div className={classes.titleDialog}>{title}</div>
      </DialogTitle>
      <DialogContent className={classes.root}>
        <div
          className={clsx(
            classes.searchIcon,
            isFocus && classes.searchIconFocused
          )}
        >
          <LineIcon icon={'ico-search-o'} />
        </div>
        <InputBaseWrapper
          placeholder={placeholder}
          classes={{
            root: classes.inputRoot,
            input: classes.inputInput
          }}
          value={value}
          name="searchBox"
          autoComplete="off"
          autoCapitalize="off"
          inputProps={{
            'aria-label': 'search',
            'data-testid': 'search_box_in_module'
          }}
          onFocus={handleFocus}
          onChange={handChange}
          onKeyDown={handleKeyDown}
          onBlur={handleBlur}
        />
      </DialogContent>
    </Dialog>
  );
}

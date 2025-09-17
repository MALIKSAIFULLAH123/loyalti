import { ClickOutsideListener } from '@metafox/ui';
import { Paper, Popper } from '@mui/material';
import React from 'react';
import { RemoteFormBuilder } from '@metafox/form';
import { RemoteDataSource } from '@metafox/framework';
import { isEmpty } from 'lodash';

export default function ImageCaptchaPopup({
  open,
  setOpen,
  onSubmit,
  anchorRef,
  dataSource,
  error
}: {
  open: boolean;
  setOpen: (x: boolean) => void;
  onSubmit: (x: any) => void;
  anchorRef: any;
  dataSource: RemoteDataSource;
  error?: any;
}) {
  const handleClickOutside = () => {
    if (!isEmpty(error)) return;

    setOpen(false);
  };

  if (!dataSource) return null;

  return (
    <ClickOutsideListener onClickAway={handleClickOutside}>
      <Popper
        open={open}
        anchorEl={anchorRef.current}
        placement={'top-start'}
        sx={{ zIndex: 2 }}
      >
        <Paper elevation={3} sx={{ p: 1 }} onClick={e => e.stopPropagation()}>
          <RemoteFormBuilder
            dataSource={dataSource}
            onSubmit={onSubmit}
            pageParams={{
              action_name: 'comment.create_comment',
              auto_focus: true
            }}
            navigationConfirmWhenDirty={false}
            onCancel={() => setOpen(false)}
          />
        </Paper>
      </Popper>
    </ClickOutsideListener>
  );
}

import { LineIcon } from '@metafox/ui';
import { IconButton, Tooltip } from '@mui/material';
import React from 'react';

const DefaultControl = React.forwardRef(
  ({ onClick, title, icon }: any, ref: any) => {
    return (
      <Tooltip title={title}>
        <IconButton
          onClick={onClick}
          size="small"
          ref={ref}
          role="button"
          sx={{
            p: 0.5,
            color: theme => {
              return theme.palette.mode === 'light'
                ? 'rgba(0, 0, 0, 0.54)'
                : '#fff';
            }
          }}
        >
          <LineIcon icon={icon} />
        </IconButton>
      </Tooltip>
    );
  }
);
export default DefaultControl;

/**
 * @type: formElement
 * name: form.element.ErrorTooltip
 */
import WarningIcon from '@mui/icons-material/Warning';
import { styled, Tooltip } from '@mui/material';
import { useField } from 'formik';
import React, { useState } from 'react';

const StyledTooltipTitle = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

type ErrorTooltipProps = {
  children: any;
  name: string;
  showErrorTooltip?: boolean;
};

const ErrorTooltip = ({
  children,
  name,
  showErrorTooltip
}: ErrorTooltipProps) => {
  const [open, setOpen] = useState(false);
  const [, meta] = useField(name);

  if (!showErrorTooltip) return children;

  return (
    <Tooltip
      disableHoverListener
      open={open && !!meta?.error}
      onClose={() => setOpen(false)}
      onOpen={() => setOpen(true)}
      componentsProps={{
        tooltip: {
          sx: {
            backgroundColor: theme => theme.palette.background.paper,
            borderStyle: 'solid',
            color: theme => theme.palette.error.main,
            '& .MuiTooltip-arrow': {
              color: theme => theme.palette.error.main
            }
          }
        }
      }}
      title={
        <StyledTooltipTitle>
          <WarningIcon sx={{ marginRight: 0.5 }} fontSize="inherit" />{' '}
          {meta?.error}
        </StyledTooltipTitle>
      }
    >
      {children}
    </Tooltip>
  );
};

export default ErrorTooltip;

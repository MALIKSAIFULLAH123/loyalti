import React from 'react';
import CheckIcon from '@mui/icons-material/Check';
import ErrorIcon from '@mui/icons-material/Error';
import WarningIcon from '@mui/icons-material/Warning';
import InfoIcon from '@mui/icons-material/Info';

const presets = {
  success: {
    sx: { color: '#28a745' }
  },
  info: {
    sx: { color: '#28a745' }
  },
  warning: {
    sx: { color: '#ffc107' }
  },
  error: {
    icon: 'ico-close',
    sx: { color: '#dc3545' }
  }
};

const ReportIcon = ({
  variant = 'success'
}: {
  variant: 'error' | 'warning' | 'success' | 'info';
}) => {
  switch (variant) {
    case 'success':
      return <CheckIcon data-testid="iconSuccess" {...presets.success} />;
    case 'error':
      return <ErrorIcon data-testid="iconError" {...presets.error} />;
    case 'warning':
      return <WarningIcon data-testid="iconWarning" {...presets.warning} />;
    default:
      return <InfoIcon data-testid="iconInfo" {...presets.info} />;
  }
};

export default ReportIcon;

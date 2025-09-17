import { LineIcon } from '@metafox/ui';
import { styled, Tooltip, TooltipProps, Typography } from '@mui/material';
import tooltipClasses from '@mui/material/Tooltip/tooltipClasses';
import React from 'react';

const HtmlTooltip = styled(({ className, ...props }: TooltipProps) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  [`& .${tooltipClasses.tooltip}`]: {
    backgroundColor: theme.palette.background.paper,
    color: theme.palette.text.primary,
    maxWidth: 360,
    boxShadow: theme.shadows['2']
  }
}));

export default function Hint({
  children,
  icon = 'ico-question-circle-o'
}: {
  children?: string;
  icon?: string;
}) {
  if (!children) return null;

  return (
    <HtmlTooltip
      enterDelay={500}
      arrow={false}
      title={
        <Typography variant="body1" sx={{ p: 1 }}>
          {children}
        </Typography>
      }
    >
      <Typography
        component="span"
        color="primary"
        variant="inherit"
        role="button"
      >
        <LineIcon icon={icon} />
      </Typography>
    </HtmlTooltip>
  );
}

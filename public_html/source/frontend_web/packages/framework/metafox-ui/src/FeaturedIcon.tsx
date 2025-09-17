import React from 'react';
import { LineIcon } from '@metafox/ui';
import { styled } from '@mui/material';

const FeaturedIcon = styled(LineIcon, { name: 'FeaturedIcon' })(
  ({ theme }) => ({
    color: theme.palette.primary.main,
    marginLeft: theme.spacing(1),
    fontSize: theme.mixins.pxToRem(16)
  })
);

export default function SponsorFlag({ value, icon, ...rest }) {
  if (!value) return null;

  return <FeaturedIcon icon={icon} {...rest} />;
}

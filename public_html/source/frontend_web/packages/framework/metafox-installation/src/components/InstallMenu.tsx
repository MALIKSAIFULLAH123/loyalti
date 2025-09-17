import React from 'react';
import { Box, styled, Typography } from '@mui/material';
import { RouteLink } from '@metafox/framework';
import { useInstallationState } from '@metafox/installation/hooks';

const UL = styled('ul', {
  name: 'StepMenu',
  slot: 'Root'
})(() => ({
  listStyle: 'none none outside',
  margin: 0,
  padding: '16px 0 16px 0'
}));

const LI = styled('li', {
  name: 'StepMenu',
  slot: 'Item'
})(() => ({
  lineHeight: '40px',
  paddingLeft: '24px'
}));

const styles = {
  active: {
    lineHeight: '32px',
    color: 'text.primary',
    fontWeight: 'bold'
  },
  default: {
    lineHeight: '32px',
    color: 'text.primary'
  }
};

export function StepMenuItem({ title, clickable, active, id }) {
  if (clickable) {
    return (
      <LI>
        <RouteLink to={`?step=${id}`}>
          <Typography sx={active ? styles.active : styles.default}>
            {title}
          </Typography>
        </RouteLink>
      </LI>
    );
  }

  return (
    <LI>
      <Typography sx={active ? styles.active : styles.default}>
        {title}
      </Typography>
    </LI>
  );
}

export default function InstallMenu() {
  const {
    steps: items,
    disabledStep,
    currentStep: found
  } = useInstallationState();

  return (
    <Box sx={{ width: '160px', pt: 0.5 }}>
      <UL>
        {items.map((item, index) => (
          <StepMenuItem
            {...item}
            active={index === found}
            key={item.title}
            clickable={index <= found && !disabledStep}
          />
        ))}
      </UL>
    </Box>
  );
}

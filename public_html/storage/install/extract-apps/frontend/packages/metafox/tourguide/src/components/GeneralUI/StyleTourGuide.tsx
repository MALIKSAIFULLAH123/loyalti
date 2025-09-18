import useTourGuideContext from '@metafox/tourguide/hooks';
import { TourGuideStep } from '@metafox/tourguide/types';
import { GlobalStyles } from '@mui/material';
import React from 'react';

function StyleTourGuide() {
  const { createStep } = useTourGuideContext();

  return (
    <GlobalStyles
      styles={{
        ...(createStep === TourGuideStep.SelectElement && {
          '&.MuiAutocomplete-popper[role="presentation"],&.MuiPopover-root': {
            display: 'none !important'
          }
        }),
        ...(createStep === TourGuideStep.InputInfoStep && {
          '&.MuiAutocomplete-popper[role="presentation"],&.MuiPopover-root': {
            display: 'inherit !important'
          }
        })
      }}
    />
  );
}

export default StyleTourGuide;

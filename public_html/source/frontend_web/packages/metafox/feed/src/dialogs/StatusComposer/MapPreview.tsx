import React from 'react';
import { Box } from '@mui/material';
import StyledIconButton from '@metafox/ui/StyledIconButton';
import { useGlobal } from '@metafox/framework';

const MapPreview = ({ composerRef, composerState }) => {
  const { i18n, jsxBackend } = useGlobal();
  const MapView = jsxBackend.get('ui.mapDisplay');
  const data = composerState?.tags?.place;
  const location = data?.value;

  const handleRemoveMap = () => {
    composerRef.current?.setTags('place', {
      ...data,
      value: { ...data?.value, show_map: false }
    });
  };

  if (!MapView || !location) return null;

  return (
    <Box sx={{ position: 'relative' }}>
      <MapView location={location} />
      <Box sx={{ position: 'absolute', top: 8, right: 8 }}>
        <StyledIconButton
          color="inherit"
          icon="ico-close"
          size="small"
          title={i18n.formatMessage({ id: 'remove' })}
          onClick={handleRemoveMap}
        />
      </Box>
    </Box>
  );
};

export default MapPreview;

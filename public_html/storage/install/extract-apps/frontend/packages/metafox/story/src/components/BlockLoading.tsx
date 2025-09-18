import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { CircularProgress, Box, Fab } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const BlockLoadingBackdrop = styled('div', { name: 'BlockLoading' })(
  ({ theme }) => ({
    position: 'absolute',
    left: 0,
    top: 0,
    bottom: 0,
    right: 0,
    zIndex: '9999',
    color: theme.palette.text.secondary,
    background:
      theme.palette.mode === 'light'
        ? 'rgba(255, 255, 255, 0.6) !important'
        : 'rgba(0, 0, 0, 0.3) !important'
  })
);

const BlockLoadingContent = styled(Box, {
  name: 'BlockLoading',
  slot: 'Content'
})({
  position: 'absolute',
  left: '50%',
  top: '50%',
  marginLeft: -20,
  marginTop: -20
});

export default function BlockLoadingComponent() {
  return (
    <BlockLoadingBackdrop>
      <BlockLoadingContent>
        <CircularProgress color="inherit" size={40} />
      </BlockLoadingContent>
    </BlockLoadingBackdrop>
  );
}

export function BlockUploadingFile(props: { progressFile?: any }) {
  const { useTheme } = useGlobal();

  const theme = useTheme();

  const { progressFile = 100 } = props || {};

  return (
    <Box
      sx={{
        position: 'absolute',
        top: 0,
        left: 0,
        bottom: 0,
        right: 0,
        background:
          theme.palette.mode === 'dark'
            ? 'rgba(0,0,0,0.5)'
            : 'rgba(255,255,255,0.5)',
        zIndex: 5,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
      }}
    >
      <Box sx={{ position: 'relative' }}>
        <Fab
          disabled
          disableRipple
          disableFocusRipple
          aria-label="save"
          color="primary"
          sx={{ backgroundColor: 'transparent !important' }}
        >
          <LineIcon
            icon="ico-photo-plus-o"
            sx={{ fontSize: 20, color: '#fff' }}
          />
        </Fab>
        <CircularProgress
          size={67}
          sx={{
            position: 'absolute',
            top: -6,
            left: -6,
            zIndex: 1
          }}
          variant="determinate"
          value={progressFile}
        />
      </Box>
    </Box>
  );
}

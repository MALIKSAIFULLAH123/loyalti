/**
 * @type: block
 * name: user.block.AdminUserLogin
 * title: AdminCP - Authentication Panel
 * bundle: admincp
 * experiment: true
 */

import {
  BlockViewProps,
  createBlock,
  useResourceForm,
  useWindowSize
} from '@metafox/framework';
import { FormBuilder } from '@metafox/form';
import React from 'react';
import { Box, Typography } from '@mui/material';

function AdminAuthPage() {
  const initialValues = {};
  const html = document.documentElement;
  const body = document.body;
  const root = document.getElementById('root');
  const formSchema = useResourceForm('user', 'user', 'login');
  const size = useWindowSize();

  // updated color
  React.useEffect(() => {
    body.setAttribute('style', 'height:100%');
    root.setAttribute('style', 'height:100%');
    html.setAttribute('style', 'height:100%');

    return () => {
      root.setAttribute('style', '');
      html.setAttribute('style', '');
      body.setAttribute('style', '');
    };
  });

  return (
    <Box
      sx={{
        maxWidth: '480px',
        margin: '0px auto',
        WebkitBoxPack: 'center',
        justifyContent: 'center',
        WebkitBoxAlign: 'center',
        alignItems: 'center',
        display: 'flex',
        flexDirection: 'column',
        width: '100%',
        minHeight: `${size[1]}px`
      }}
      data-testid="layoutSlotMain"
    >
      <Box
        sx={{
          backgroundColor: 'rgb(255, 255, 255)',
          color: 'rgba(0, 0, 0, 0.87)',
          transition: 'box-shadow 300ms cubic-bezier(0.4, 0, 0.2, 1) 0ms',
          borderRadius: 1,
          boxShadow: 2,
          p: 4,
          width: '100%'
          // marginTop: '-150px' // how to keep input centered screen without overlap top?
        }}
      >
        {formSchema ? (
          <FormBuilder
            submitAction="@login"
            formSchema={formSchema}
            initialValues={initialValues}
          />
        ) : (
          <Box>
            <Typography variant="h4">Whoops, Something went wrong</Typography>
            <Typography sx={{ mt: 1 }}>
              :( Reload browser to try again!
            </Typography>
          </Box>
        )}
      </Box>
    </Box>
  );
}

export default createBlock<BlockViewProps>({
  extendBlock: AdminAuthPage
});

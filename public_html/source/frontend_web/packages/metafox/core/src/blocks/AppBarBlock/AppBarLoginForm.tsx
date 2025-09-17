import { useGlobal, useResourceForm } from '@metafox/framework';
import { FormBuilder } from '@metafox/form';
import React from 'react';
import { Button, styled, useMediaQuery, useTheme } from '@mui/material';

const ButtonSignIn = styled(Button, { name: 'ButtonSignIn' })(({ theme }) => ({
  fontSize: 15,
  height: 32,
  padding: theme.spacing(0, 3),
  textTransform: 'capitalize',
  position: 'absolute',
  right: theme.spacing(2)
}));

export default function AppBarLoginForm() {
  const formSchema = useResourceForm('user', 'user', 'small_login');
  const { navigate, i18n } = useGlobal();
  const theme = useTheme();
  const isMediaScreen = useMediaQuery(theme.breakpoints.down('lg'));

  const signInButtonOnClick = () => {
    navigate({
      pathname: '/login'
    });
  };

  if (isMediaScreen) {
    return (
      <ButtonSignIn
        variant="contained"
        color="primary"
        size="small"
        onClick={signInButtonOnClick}
        disableElevation
        type="submit"
      >
        {i18n.formatMessage({ id: 'sign_in' })}
      </ButtonSignIn>
    );
  }

  return (
    <FormBuilder formSchema={formSchema} navigationConfirmWhenDirty={false} />
  );
}

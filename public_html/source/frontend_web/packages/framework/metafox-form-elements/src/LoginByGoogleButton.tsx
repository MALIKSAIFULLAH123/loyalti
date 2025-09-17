/**
 * @type: formElement
 * name: form.element.LoginByGoogleButton
 * chunkName: formBasic
 */
import { Typography, FormControl, styled } from '@mui/material';
import MuiButton from '@mui/material/Button';
import { camelCase } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { useScript, useGlobal } from '@metafox/framework';

const name = 'LoginByTikTokButton';

const ButtonStyled = styled(MuiButton, { name, slot: 'button' })(
  ({ theme }) => ({
    ...(theme.palette.mode === 'dark' && {
      borderColor: 'rgb(255, 255, 255)',
      '&:hover': {
        borderColor: 'rgb(255, 255, 255)'
      }
    })
  })
);

const LabelStyled = styled(Typography, { name, slot: 'label' })(
  ({ theme }) => ({
    marginLeft: theme.spacing(1),
    ...(theme.palette.mode === 'dark' && {
      color: 'rgb(255, 255, 255)',
      '&:hover': {
        color: 'rgb(255, 255, 255)'
      }
    })
  })
);

function LoginByGoogleButton({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    disabled,
    controlProps = {},
    color = 'primary',
    margin,
    size = 'large',
    fullWidth = false,
    className,
    variant = 'outlined',
    sx,
    icon,
    label,
    labelProps = { variant: 'h5' },
    isShowLabel
  } = config;

  const status = useScript('https://accounts.google.com/gsi/client');
  const ref = React.useRef();

  const { getSetting, dispatch } = useGlobal();

  const [tokenClient, setTokenClient] = React.useState({});
  const client_id = getSetting('socialite.google.client_id');

  const handleClick = () => {
    tokenClient.requestAccessToken();
  };

  const handleCallback = res => {
    dispatch({
      type: 'login/callback',
      payload: { accessToken: res.access_token, provider: 'google' }
    });
  };

  React.useEffect(() => {
    if (status === 'ready') {
      setTokenClient(
        google.accounts.oauth2.initTokenClient({
          client_id,
          scope: 'email profile',
          callback: handleCallback
        })
      );
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [status]);

  const disabledButton = disabled || formik.isSubmitting || forceDisabled;

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      {...controlProps}
      sx={sx}
      data-testid={camelCase(`field ${name}`)}
    >
      <ButtonStyled
        fullWidth={fullWidth}
        variant={variant}
        role="button"
        size={size}
        type="button"
        color={color}
        className={className}
        disabled={disabledButton}
        data-testid={camelCase(`button ${name}`)}
        onClick={handleClick}
        ref={ref}
      >
        <img
          src={icon}
          alt="google"
          width={32}
          style={disabledButton ? { filter: 'grayscale(1)' } : null}
        />
        {isShowLabel && label && (
          <LabelStyled {...labelProps}>{label}</LabelStyled>
        )}
      </ButtonStyled>
    </FormControl>
  );
}
export default LoginByGoogleButton;

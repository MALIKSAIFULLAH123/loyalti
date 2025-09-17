/**
 * @type: formElement
 * name: form.element.LoginByFacebookButton
 * chunkName: formBasic
 */
import { FormControl, Typography } from '@mui/material';
import MuiButton from '@mui/material/Button';
import { camelCase } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { useScript, useGlobal } from '@metafox/framework';

function LoginByFacebookButton({
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
    variant = 'contained',
    sx,
    icon,
    label,
    labelProps = { variant: 'h5' },
    isShowLabel
  } = config;

  const status = useScript('https://connect.facebook.net/en_US/sdk.js');
  const ref = React.useRef();

  const { getSetting, dispatch } = useGlobal();

  const appId = getSetting('socialite.facebook.client_id');

  const handleClick = () => {
    FB.login(response => {
      if (response?.authResponse)
        dispatch({
          type: 'login/callback',
          payload: {
            accessToken: response?.authResponse?.accessToken,
            provider: 'facebook'
          }
        });
    });
  };

  React.useEffect(() => {
    if (status === 'ready') {
      FB.init({
        appId,
        autoLogAppEvents: true,
        xfbml: true,
        version: 'v16.0'
      });
    }
  }, [status]);

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      {...controlProps}
      sx={sx}
      data-testid={camelCase(`field ${name}`)}
    >
      <MuiButton
        fullWidth={fullWidth}
        variant={variant as any}
        role="button"
        color={color}
        size={size}
        type="button"
        className={className}
        disabled={disabled || formik.isSubmitting || forceDisabled}
        data-testid={camelCase(`button ${name}`)}
        onClick={handleClick}
        ref={ref}
      >
        <img src={icon} alt="facebook" width={32} />
        {isShowLabel && label && (
          <Typography ml={1} {...labelProps}>
            {label}
          </Typography>
        )}
      </MuiButton>
    </FormControl>
  );
}
export default LoginByFacebookButton;

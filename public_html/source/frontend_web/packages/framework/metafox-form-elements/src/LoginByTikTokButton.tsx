/**
 * @type: formElement
 * name: form.element.LoginByTikTokButton
 * chunkName: formBasic
 */
import { FormControl, Typography, styled } from '@mui/material';
import MuiButton from '@mui/material/Button';
import { camelCase } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { useGlobal, useLocation } from '@metafox/framework';

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
const COOKIE_CHECK = 'tiktok_login';

function LoginByTikTokButton({
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

  const ref = React.useRef();

  const { getSetting, dispatch, cookieBackend } = useGlobal();

  const { search } = useLocation();
  const code = new URLSearchParams(search).get('code');

  const appId = getSetting('socialite.tiktok.client_id');
  const redirect_uri = getSetting('socialite.tiktok.redirect');

  const checked = cookieBackend.get(COOKIE_CHECK) === '1';

  const handleClick = event => {
    event.preventDefault();
    cookieBackend.set(COOKIE_CHECK, '1');
    const csrfState = Math.random().toString(36).substring(2);
    const url = `https://www.tiktok.com/v2/auth/authorize?client_key=${appId}&scope=user.info.basic&response_type=code&redirect_uri=${redirect_uri}&state=${csrfState}`;

    window.location.href = url;

    return false;
  };

  React.useEffect(() => {
    if (!checked) return;

    cookieBackend.remove(COOKIE_CHECK);

    if (code) {
      dispatch({
        type: 'login/callback',
        payload: { code, provider: 'tiktok' }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [code, dispatch]);

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
        color={color}
        size={size}
        type="button"
        className={className}
        disabled={disabledButton}
        data-testid={camelCase(`button ${name}`)}
        onClick={handleClick}
        ref={ref}
      >
        <img
          src={icon}
          alt="TikTok"
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
export default LoginByTikTokButton;

/**
 * @type: formElement
 * name: form.element.ImageCaptcha
 * chunkName: formElement
 */

import { FormFieldProps } from '@metafox/form';
import {
  FormControl,
  TextField,
  Box,
  CircularProgress,
  IconButton,
  useTheme,
  styled
} from '@mui/material';
import { useField } from 'formik';
import { camelCase, isEmpty } from 'lodash';
import React from 'react';
import ErrorMessage from '../ErrorMessage';
import { useGlobal, useResourceAction } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';

const CaptchaContent = styled(Box, {
  name: 'CaptchaContent'
})(({ theme }) => ({
  '& img': {
    maxWidth: '100%'
  }
}));

const ImageCaptchaField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps) => {
  const {
    margin,
    fullWidth,
    disabled,
    img,
    key,
    required = true,
    label,
    placeholder,
    actionName,
    autoFocus
  } = config;
  const [field, meta] = useField(name ?? 'image_captcha');
  const { apiClient, compactData } = useGlobal();
  const [loading, setLoading] = React.useState(false);
  const [srcImage, setSrcImage] = React.useState(img);
  const processing = React.useRef(false);
  const theme = useTheme();
  const [keyState, setKeyState] = React.useState(key);
  const dataSource = useResourceAction(
    'captcha',
    'image_captcha',
    'refreshCaptcha'
  );

  const reloadCaptcha = () => {
    if (loading || !dataSource) return;

    setLoading(true);
    apiClient
      .request({
        method: dataSource.apiMethod || 'POST',
        url: dataSource.apiUrl,
        data: compactData(dataSource.apiParams, { action_name: actionName })
      })
      .then(result => result.data.data)
      .then(data => {
        if (!data) return;

        setKeyState(data?.key);
        setSrcImage(data?.img);
      })
      .finally(() => {
        setLoading(false);
      });
  };

  const haveError = Boolean(meta.error && (meta.touched || formik.submitCount));

  React.useEffect(() => {
    if (formik?.isSubmitting && formik?.isValid) {
      processing.current = true;
    }
  }, [formik?.isSubmitting, formik?.isValid]);

  React.useEffect(() => {
    if (
      !formik?.isSubmitting &&
      processing.current &&
      !isEmpty(formik?.errors)
    ) {
      processing.current = false;
      reloadCaptcha();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [processing.current, formik?.isSubmitting, formik?.errors]);

  React.useEffect(() => {
    if ((meta?.touched || autoFocus) && keyState) {
      formik.setFieldValue('image_captcha_key', keyState, false);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [meta?.touched, keyState, autoFocus]);

  React.useEffect(() => {
    if (!keyState) {
      reloadCaptcha();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (disabled || forceDisabled) {
    return null;
  }

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
    >
      <Box sx={{ display: 'flex' }}>
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <Box sx={{ maxWidth: '50%' }}>
            <TextField
              {...field}
              error={haveError}
              required={required || forceRequired}
              inputProps={{
                'data-testid': camelCase(`input ${name}`)
              }}
              label={label}
              placeholder={placeholder}
              autoFocus={autoFocus}
            />
          </Box>
          <CaptchaContent
            ml={1}
            sx={{
              flex: 1,
              minWidth: 0,
              position: 'relative',
              display: 'flex'
            }}
          >
            {loading ? (
              <Box
                sx={{
                  display: 'flex',
                  justifyContent: 'center',
                  alignItems: 'center',
                  position: 'absolute',
                  left: 0,
                  top: 0,
                  right: 0,
                  bottom: 0,
                  background:
                    theme.palette.mode === 'dark'
                      ? 'rgba(0,0,0,0.5)'
                      : 'rgba(255,255,255,0.5)'
                }}
              >
                <CircularProgress size={16} />
              </Box>
            ) : null}
            <img src={srcImage} alt="" />
          </CaptchaContent>
          <Box sx={{ minWidth: '40px' }}>
            <IconButton size="medium" color="primary" onClick={reloadCaptcha}>
              <LineIcon icon="ico-rotate-right" />
            </IconButton>
          </Box>
        </Box>
      </Box>
      {haveError ? <ErrorMessage error={meta.error} /> : null}
    </FormControl>
  );
};

export default ImageCaptchaField;

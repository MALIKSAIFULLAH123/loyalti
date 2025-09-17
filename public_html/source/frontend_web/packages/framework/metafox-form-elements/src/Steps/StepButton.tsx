/**
 * @type: formElement
 * name: form.element.StepButton
 * chunkName: formBasic
 */
import { FormControl } from '@mui/material';
import MuiButton from '@mui/lab/LoadingButton';
import { camelCase, isEmpty } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { setNestedObjectValues, useFormikContext } from 'formik';
import { FORM_UPLOAD_FILE, useGlobal } from '@metafox/framework';

function StepButton({ config, name, disabled: forceDisabled }: FormFieldProps) {
  const {
    type = 'button',
    disabled,
    color = 'primary',
    margin,
    size,
    variant = 'outlined',
    label,
    stepValueName,
    actionType,
    fullWidth,
    sxFieldWrapper
  } = config;
  const { dispatch } = useGlobal();
  const formik = useFormikContext();
  const { current_step } = formik?.values || {};
  const isNextButton = actionType === 'next';
  const valueStep = isNextButton ? 1 : -1;
  const disabledState = disabled || formik?.isSubmitting || forceDisabled;
  const [loading, setLoading] = React.useState(false);

  const handleClick = () => {
    if (!formik) return;

    if (isNextButton) {
      formik.validateForm().then(err => {
        if (isEmpty(err)) {
          setLoading(true);
          formik.setSubmitting(true);
          dispatch({
            type: FORM_UPLOAD_FILE,
            payload: {
              values: formik.values,
              form: formik
            },
            meta: {
              onSuccess: () => {
                formik.setFieldValue(stepValueName, current_step + valueStep);
                setLoading(false);
                formik.setSubmitting(false);
              },
              onFailure: () => {
                setLoading(false);
                formik.setSubmitting(false);
              }
            }
          });
        } else {
          formik.setTouched(setNestedObjectValues(err, true));
        }
      });

      return;
    }

    formik.setFieldValue(stepValueName, current_step + valueStep);
  };

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
    >
      <MuiButton
        loading={loading}
        variant={variant}
        role="button"
        color={color}
        size={size}
        type={type}
        disabled={disabledState}
        onClick={handleClick}
      >
        {label}
      </MuiButton>
    </FormControl>
  );
}
export default StepButton;

/**
 * @type: formElement
 * name: form.element.SubmitAction
 * chunkName: formBasic
 */
import { FormFieldProps, useFormSchema } from '@metafox/form';
import { FORM_SUBMIT_ACTION, useGlobal } from '@metafox/framework';
import { Button as MuiButton, FormControl } from '@mui/material';
import { useFormikContext } from 'formik';
import { camelCase, isEmpty } from 'lodash';
import React from 'react';

const SubmitAction = ({
  config,
  disabled: forceDisabled,
  formik
}: FormFieldProps) => {
  const {
    sxFieldWrapper,
    type = 'button',
    variant,
    disabled,
    color,
    size,
    label,
    className,
    margin,
    fullWidth,
    name,
    onClick,
    customAction
  } = config;

  const { values, errors } = useFormikContext();
  const { dialog, onCancel } = useFormSchema();
  const { dispatch } = useGlobal();
  const { useDialog } = useGlobal();
  const { closeDialog } = useDialog();

  const handleFormBehavior = () => {
    if (onCancel) {
      onCancel();
    } else if (onClick) {
      onClick();
    } else if (dialog) {
      closeDialog();
    }
  };

  const handleClick = () => {
    if (!isEmpty(errors)) return;

    dispatch({
      type: customAction?.type || FORM_SUBMIT_ACTION,
      payload: {
        ...customAction,
        values
      }
    });
    handleFormBehavior();
  };

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
    >
      <MuiButton
        fullWidth={fullWidth}
        variant={variant as any}
        color={color}
        size={size}
        type={type}
        className={className}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        data-testid={camelCase(`button ${name}`)}
        onClick={handleClick}
      >
        {label}
      </MuiButton>
    </FormControl>
  );
};

export default SubmitAction;

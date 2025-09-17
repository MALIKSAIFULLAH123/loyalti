/**
 * @type: formElement
 * name: form.element.CustomButton
 * chunkName: formBasic
 */
import { FormFieldProps, useFormSchema } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { Button as MuiButton, FormControl } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';

const CancelButtonField = ({
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
    isFinally,
    margin,
    fullWidth,
    name,
    customAction
  } = config;
  const { dialog, onCancel, handleBackForm } = useFormSchema();
  const { dispatch } = useGlobal();
  const { useDialog } = useGlobal();
  const { closeDialog } = useDialog();
  const isBackStepForm =
    handleBackForm && customAction?.type === 'multiStepForm/previous';

  const handleFormBehavior = () => {
    if (onCancel) {
      onCancel();
    } else if (dialog) {
      closeDialog();
    }
  };

  const handleClick = React.useCallback(() => {
    if (isBackStepForm) {
      handleBackForm({
        type: customAction.type,
        payload: customAction.payload
      });

      return;
    }

    dispatch({ type: customAction.type, payload: customAction.payload });

    if (isFinally) {
      handleFormBehavior();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [customAction.type, customAction.payload, isFinally, handleBackForm]);

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

export default CancelButtonField;

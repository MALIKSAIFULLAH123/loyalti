/**
 * @type: formElement
 * name: form.element.Cancel
 * chunkName: formBasic
 */
import { FormFieldProps, useFormSchema } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import {
  Button as MuiButton,
  FormControl,
  useMediaQuery,
  useTheme
} from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';

const CancelButtonField = ({
  config,
  disabled: forceDisabled,
  formik
}: FormFieldProps) => {
  const {
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
    noConfirmation,
    action,
    sxFieldWrapper
  } = config;
  const { dialog, onCancel } = useFormSchema();
  const { navigate, dialogBackend, i18n, setNavigationConfirm, dispatch } =
    useGlobal();
  const { useDialog } = useGlobal();
  const { closeDialog, forceClose } = useDialog();
  const { dirty, touched } = formik;

  const handleFormBehavior = () => {
    if (action?.type) {
      dispatch(action);

      return;
    }

    if (onCancel) {
      onCancel();
    } else if (onClick) {
      onClick();
    } else if (dialog) {
      closeDialog();
    } else {
      navigate(-1);
    }
  };

  const handleClick = React.useCallback(() => {
    if (dirty && Object.keys(touched).length > 0 && !noConfirmation) {
      dialogBackend
        .confirm({
          title: i18n.formatMessage({ id: 'are_you_sure' }),
          message: i18n.formatMessage({
            id: 'if_you_leave_form_no_save_changed'
          })
        })
        .then(ok => {
          if (!ok) return;

          if (dialog) {
            forceClose();

            return;
          }

          setNavigationConfirm(false);
          handleFormBehavior();
        })
        .catch(void 0);
    } else {
      handleFormBehavior();
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [closeDialog, dialog, onClick, dirty, touched]);

  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));

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
        size={isSmallScreen ? 'medium' : size}
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

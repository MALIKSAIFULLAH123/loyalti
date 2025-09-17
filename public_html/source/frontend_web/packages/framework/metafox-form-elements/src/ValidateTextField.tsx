/**
 * @type: formElement
 * name: form.element.ValidateText
 * chunkName: formBasic
 */
import { FormFieldProps, useFormSchema } from '@metafox/form';
import {
  FormControl,
  InputAdornment,
  styled,
  TextField,
  TextFieldProps
} from '@mui/material';
import { useField } from 'formik';
import { camelCase, get, isString } from 'lodash';
import Description from './Description';
import ErrorTooltip from './ErrorTooltip';
import Warning from './Warning';
import React from 'react';
import { GlobalState, useGlobal } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { compactData } from '@metafox/utils';

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& p, input::placeholder': {
    color: theme.palette.text.hint
  }
}));

const ValidateTextField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const { apiClient } = useGlobal();
  const [loading, setLoading] = React.useState<boolean>(false);
  const [errorBackend, setErrorBackend] = React.useState<string>('');

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [field, meta, { setValue, setError }] = useField({
    name: name ?? 'TextField'
  });
  const action = useSelector((state: GlobalState) =>
    get(state, `_actions.${config?.validateAction}`)
  );
  const { setPreventSubmit } = useFormSchema();

  const {
    label,
    disabled,
    autoComplete,
    placeholder,
    variant,
    margin,
    fullWidth,
    type = 'text',
    size,
    required,
    description,
    autoFocus,
    readOnly,
    maxLength,
    showErrorTooltip = false,
    sx,
    sxFieldWrapper,
    order,
    preventScrolling = false,
    startAdornment,
    endAdornment,
    minNumber,
    maxNumber,
    warning,
    hoverState,
    returnKeyType,
    defaultValue,
    component, // fix React warning.
    testid,
    showWhen,
    requiredWhen,
    enabledWhen,
    findReplace,
    inputLabelProps = {},
    className,
    validateParam,
    ...rest
  } = config;

  const haveError: boolean = !!(
    (meta.error || errorBackend) &&
    (meta.touched || formik.submitCount)
  );

  const clearError = () => {
    setErrorBackend('');
    setPreventSubmit(false);
  };

  const handleBlur = e => {
    const data = field.value?.trim();

    isString(field.value) && setValue(data);
    field.onBlur(e);

    if (!field.value || meta.error || errorBackend) {
      return;
    }

    setLoading(true);
    setPreventSubmit(true);

    apiClient
      .request({
        method: action.apiMethod,
        url: action.apiUrl,
        params: compactData(action.apiParams, {
          [validateParam || name]: data
        }),
        data: { [validateParam || name]: data }
      })
      .then(value => {
        clearError();
      })
      .catch(error => {
        const msgErr =
          get(error, `response.data.errors.${name}`) ||
          get(error, 'response.data.message');
        setErrorBackend(msgErr);
      })
      .finally(() => {
        setLoading(false);
      });
  };

  React.useEffect(() => {
    if (errorBackend) {
      clearError();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [field?.value]);

  const showError =
    !loading && !showErrorTooltip && haveError && (meta.error || errorBackend);

  let helperText = null;

  if (showError) {
    const error = meta.error || errorBackend;
    helperText = <Description text={error} error />;
  }

  return (
    <FormControl
      margin={margin}
      variant={variant}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
      id={name}
    >
      <ErrorTooltip name={field.name} showErrorTooltip={showErrorTooltip}>
        <Text
          {...rest}
          // careful change this value, can make shrink overlap when autofill data
          // dont use (field.value ?? '') => it make issue on some case(ex: browser autofill data)
          value={field.value ?? ''}
          name={field.name}
          onChange={field.onChange}
          onWheel={e => preventScrolling && e.target?.blur()}
          error={haveError}
          disabled={disabled || forceDisabled || formik.isSubmitting || loading}
          required={required || forceRequired}
          size={size}
          onBlur={handleBlur}
          InputProps={{
            startAdornment: startAdornment ? (
              <InputAdornment position="start">{startAdornment}</InputAdornment>
            ) : null,
            endAdornment: endAdornment ? (
              <InputAdornment position="end">{endAdornment}</InputAdornment>
            ) : null
          }}
          inputProps={{
            readOnly,
            autoFocus,
            autoComplete,
            maxLength,
            'data-testid': camelCase(`input ${name}`)
          }}
          // careful change this InputLabelProps, can make shrink overlap when autofill data
          InputLabelProps={{
            shrink: field.value || undefined,
            ...Object.assign({}, inputLabelProps)
          }}
          label={label}
          placeholder={placeholder ?? label}
          type={type}
          defaultValue={field.value ?? defaultValue}
          helperText={helperText}
          variant={variant}
          sx={sx}
          className={className}
        />
      </ErrorTooltip>
      <Warning warning={warning} />
      <Description text={description} />
    </FormControl>
  );
};

export default ValidateTextField;

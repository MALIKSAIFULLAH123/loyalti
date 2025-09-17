/**
 * @type: formElement
 * name: form.element.ColorPicker
 * chunkName: colorPicker
 */
import { FormFieldProps } from '@metafox/form';
import {
  Box,
  FormControl,
  InputAdornment,
  Popover,
  styled,
  TextField,
  TextFieldProps
} from '@mui/material';
import { darken, lighten, rgbToHex } from '@mui/system/colorManipulator';
import { useField } from 'formik';
import { camelCase, isNil, isString } from 'lodash';
import React from 'react';
import Description from '../Description';
import Warning from '../Warning';
import ColorPopover from './ColorPopover';

const ColorPickerAdornment = styled(Box, {
  name: 'MuiButton',
  slot: 'ColorPickerAdornment'
})<{ color: string }>(({ color }) => ({
  width: 28,
  height: 28,
  border: '1px solid rgba(0,0,0,0.1)',
  backgroundColor: color,
  cursor: 'pointer'
}));

type Config = TextFieldProps & {
  picker: string;
  paletteName?: string;
};

const ColorPickerField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<Config>) => {
  const [field, meta, { setValue }] = useField(name ?? 'TextField');
  const [open, setOpen] = React.useState<boolean>(false);

  const {
    label,
    disabled,
    placeholder,
    noFeedback,
    variant,
    margin,
    fullWidth,
    size,
    required,
    description,
    showErrorTooltip = false,
    sx,
    sxFieldWrapper,
    startAdornment,
    endAdornment,
    warning,
    alwayShowDescription = true,
    contextualDescription,
    picker = 'chrome',
    paletteName,
    component,
    ...rest
  } = config;

  // Also update other color.
  // eslint-disable-next-line @typescript-eslint/no-unused-vars

  React.useEffect(() => {
    // console.log(data);
    // formik.setValues(data, false);
  }, []);

  // fix: A component is changing an uncontrolled input
  if (isNil(field.value) || !isString(field.value)) {
    field.value = config.defaultValue ?? '';
  }

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  const handleBlur = e => {
    isString(field.value) && setValue(field.value.trim());
    field.onBlur(e);
  };

  const handleChange = value => {
    if (!paletteName) {
      setValue(value);
    } else {
      setValue(value);
      const dark = rgbToHex(darken(field.value, 0.2));
      const light = rgbToHex(lighten(field.value, 0.2));
      formik.setFieldValue(`${paletteName}.dark`, dark);
      formik.setFieldValue(`${paletteName}.light`, light);
    }
  };

  const showError = !showErrorTooltip && haveError && meta.error;

  let helperText = null;

  if (description) {
    helperText = <Description text={description} />;
  }

  if (showError) {
    helperText = meta.error;
  }

  if (description && alwayShowDescription && showError) {
    helperText = (
      <>
        <Description text={description} />
        <Description text={meta.error} error />
      </>
    );
  }

  const controlRef = React.useRef();

  return (
    <FormControl
      margin={margin}
      variant={variant}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
      id={name}
      ref={controlRef}
    >
      <TextField
        {...rest}
        value={field.value}
        onChange={e => handleChange(e.currentTarget.value)}
        error={haveError}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        required={required || forceRequired}
        size={size}
        onBlur={handleBlur}
        InputProps={{
          readOnly: true,
          endAdornment: (
            <ColorPickerAdornment
              color={field.value}
              onClick={() => setOpen(true)}
            />
          ),
          startAdornment: startAdornment ? (
            <InputAdornment position="start">{startAdornment}</InputAdornment>
          ) : null
        }}
        inputProps={{
          'data-testid': camelCase(`input ${name}`)
        }}
        label={label}
        placeholder={placeholder}
        helperText={helperText}
        variant={variant}
        sx={sx}
      />
      <Popover
        disablePortal={false}
        open={open}
        anchorEl={controlRef.current}
        onClose={() => setOpen(false)}
      >
        <ColorPopover
          color={field.value}
          onChange={handleChange}
          picker={picker}
        />
      </Popover>
      <Warning warning={warning} />
    </FormControl>
  );
};

export default ColorPickerField;

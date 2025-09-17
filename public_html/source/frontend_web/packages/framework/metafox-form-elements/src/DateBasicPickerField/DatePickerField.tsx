/**
 * @type: formElement
 * name: form.element.DateBasic
 * chunkName: datePicker
 */

import { FormFieldProps } from '@metafox/form';
import { FormControl, TextField } from '@mui/material';
import { DatePicker, LocalizationProvider } from '@mui/x-date-pickers';
import { AdapterMoment } from '@mui/x-date-pickers/AdapterMoment';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import moment from 'moment';
import React from 'react';
import { styled } from '@mui/material/styles';

const FormControlStyled = styled(FormControl, {
  name: 'FormControl'
})(({ theme }) => ({
  '& .iconCalendar': {
    fontSize: theme.mixins.pxToRem(20)
  }
}));

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& input::placeholder': {
    color: theme.palette.text.hint
  }
}));

function DateBasic({
  config,
  name,
  formik,
  disabled: forceDisabled,
  required: forceRequired
}: FormFieldProps) {
  const {
    label,
    variant,
    margin,
    pickerVariant,
    component,
    value,
    required,
    minDate,
    maxDate,
    disabled,
    valueFormat = 'DD/MM/YYYY',
    viewFormat = 'L',
    size,
    sx,
    fullWidth,
    sxFieldWrapper,
    hiddenLabel,
    ...restConfig
  } = config;

  const [field, meta, { setValue, setTouched }] = useField(name ?? 'datebasic');
  const [selectedDate, setDate] = React.useState(
    field.value ? moment(field.value, valueFormat).toDate() : null
  );

  const handleDateChange = (date: any, value: string) => {
    const isValid = moment(date)?.isValid();
    setDate(isValid ? date.toISOString() : date);
  };

  const handleInputBlur = () => {
    setTouched(true);
  };

  React.useEffect(() => {
    const isValid = moment(selectedDate).isValid();

    if (!isValid) {
      setValue(selectedDate);

      return;
    }

    const newDateTime = moment(selectedDate).format(valueFormat);

    setValue(newDateTime);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedDate]);

  const haveError = Boolean(meta.error && (meta.touched || formik.submitCount));

  return (
    <FormControlStyled
      margin={margin ?? 'normal'}
      disabled={disabled}
      fullWidth={fullWidth}
      hiddenLabel={hiddenLabel}
      required={required || forceRequired}
      sx={sxFieldWrapper}
      error={haveError}
      size={size}
      variant={variant as any}
      data-testid={camelCase(`field ${name}`)}
    >
      <LocalizationProvider dateAdapter={AdapterMoment}>
        <DatePicker
          disabled={disabled || forceDisabled || formik.isSubmitting}
          value={selectedDate}
          inputFormat={viewFormat}
          onChange={handleDateChange}
          label={label}
          minDate={minDate ? new Date(minDate) : null}
          maxDate={maxDate ? new Date(maxDate) : null}
          dayOfWeekFormatter={(_day: string) => _day}
          renderInput={params => (
            <Text
              {...params}
              size={size}
              variant={variant as any}
              required={required || forceRequired}
              error={haveError}
              helperText={haveError ? meta.error : null}
              onBlur={handleInputBlur}
              inputProps={{
                ...params.inputProps,
                'data-testid': camelCase(`input ${name}`)
              }}
              sx={sx}
            />
          )}
          {...restConfig}
        />
      </LocalizationProvider>
    </FormControlStyled>
  );
}

export default DateBasic;

/**
 * @type: formElement
 * name: form.element.Date
 * chunkName: datePicker
 */

import { FormFieldProps } from '@metafox/form';
import { FormControl, TextField, styled } from '@mui/material';
import { AdapterMoment } from '@mui/x-date-pickers/AdapterMoment';
import { DatePicker, LocalizationProvider } from '@mui/x-date-pickers';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import moment from 'moment';
import React from 'react';
import ErrorMessage from '../ErrorMessage';
import useStyles from './styles';

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& input::placeholder': {
    color: theme.palette.text.hint
  }
}));

function DatePickerField({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    label,
    component,
    variant,
    disabled,
    pickerVariant,
    autoComplete = 'off',
    placeholder,
    startOfDay: _start,
    endOfDay: _end,
    minDate,
    maxDate,
    sxFieldWrapper,
    size,
    views = ['year', 'day'],
    required,
    ...restConfig
  } = config;

  const classes = useStyles();

  const [field, meta, { setValue, setTouched }] = useField(name ?? 'date');
  const haveError = Boolean(meta.error && (meta.touched || formik.submitCount));

  const handleDateChange = (date: moment.Moment, value: string) => {
    const text = date;

    if (!moment.isMoment(date)) {
      date = moment(date);
    }

    setTouched(true);

    const isValidDate = date.isValid();

    if (isValidDate) {
      if (date && _start) {
        date = date.startOf('day');
      } else if (date && _end) {
        date = date.endOf('day');
      }

      setValue(date ? date.toISOString() : undefined);
    } else {
      setValue(text);
    }
  };

  const onBlurPicker = () => {
    setTouched(true);
  };

  return (
    <FormControl
      margin="dense"
      className={classes.root}
      data-testid={camelCase(`button ${name}`)}
      sx={sxFieldWrapper}
      size={size}
    >
      <LocalizationProvider dateAdapter={AdapterMoment}>
        <DatePicker
          views={views}
          value={field.value ?? null}
          onChange={handleDateChange}
          label={label}
          minDate={minDate ? new Date(minDate) : null}
          maxDate={maxDate ? new Date(maxDate) : null}
          disabled={disabled || forceDisabled || formik.isSubmitting}
          dayOfWeekFormatter={(_day: string) => _day}
          renderInput={params => (
            <Text
              {...params}
              autoComplete={autoComplete}
              data-testid={camelCase(`input ${name}`)}
              size={size}
              onBlur={onBlurPicker}
              error={haveError}
              required={required}
            />
          )}
          {...restConfig}
        />
      </LocalizationProvider>
      {haveError ? <ErrorMessage error={meta.error} /> : null}
    </FormControl>
  );
}

export default DatePickerField;

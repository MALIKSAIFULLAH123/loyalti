import { DatePicker, LocalizationProvider } from '@mui/lab';
import AdapterDateFns from '@mui/lab/AdapterMoment';
import { TextField } from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import moment from 'moment';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import useStyles from './styles';

function TimePickerField({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    label,
    variant,
    margin,
    pickerVariant,
    component,
    disabled,
    value,
    ...restConfig
  } = config;
  const classes = useStyles();
  const [field, , { setValue }] = useField(name ?? 'TimePickerField');
  const [selectedDate, setDate] = React.useState(
    value ? value : moment().format()
  );

  const handleDateChange = (date: any, value: string) => {
    setDate(date.format());
  };

  React.useEffect(() => {
    setValue(selectedDate);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedDate]);

  return (
    <div className={classes.root} data-testid={camelCase(`field ${name}`)}>
      <LocalizationProvider dateAdapter={AdapterDateFns}>
        <DatePicker
          value={field.value}
          onChange={handleDateChange}
          label={label}
          renderInput={params => (
            <TextField {...params} data-testid={camelCase(`input ${name}`)} />
          )}
          disabled={disabled || forceDisabled || formik.isSubmitting}
          {...restConfig}
        />
      </LocalizationProvider>
    </div>
  );
}

export default TimePickerField;

import { FormFieldProps } from '@metafox/form';
import { TextField, styled } from '@mui/material';
import { DatePicker, LocalizationProvider } from '@mui/x-date-pickers';
import { AdapterMoment } from '@mui/x-date-pickers/AdapterMoment';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import moment from 'moment';
import React from 'react';
import useStyles from './styles';

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& input::placeholder': {
    color: theme.palette.text.hint
  }
}));

function BirthDay({
  config,
  name,
  formik,
  disabled: forceDisabled
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
    valueFormat = 'YYYY-MM-DD',
    viewFormat = 'L',
    ...restConfig
  } = config;

  const classes = useStyles();
  const [field, meta, { setValue, setTouched }] = useField(name ?? 'birthday');
  const [selectedDate, setDate] = React.useState(
    field.value ? moment(field.value).toDate() : null
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
    <div className={classes.root} data-testid={camelCase(`field ${name}`)}>
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
              data-testid={camelCase(`input ${name}`)}
              required={required}
              error={haveError}
              helperText={haveError ? meta.error : null}
              onBlur={handleInputBlur}
            />
          )}
          {...restConfig}
        />
      </LocalizationProvider>
    </div>
  );
}

export default BirthDay;

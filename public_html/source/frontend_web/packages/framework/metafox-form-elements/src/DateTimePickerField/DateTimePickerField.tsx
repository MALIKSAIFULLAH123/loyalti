/**
 * @type: formElement
 * name: form.element.Datetime
 * chunkName: datePicker
 */

import { Box, styled, TextField } from '@mui/material';
import { useField } from 'formik';
import { camelCase, isString } from 'lodash';
import moment from 'moment';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import ErrorMessage from '../ErrorMessage';
import TimeSuggestionPicker from './TimeSuggestionPicker';
import {
  LocalizationProvider,
  DatePicker,
  TimePicker
} from '@mui/x-date-pickers';
import { AdapterMoment } from '@mui/x-date-pickers/AdapterMoment';
import Description from '../Description';

const Root = styled('div', { name: 'DateTimePickerField' })(({ theme }) => ({
  padding: theme.spacing(2, 0, 1)
}));

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& input::placeholder': {
    color: theme.palette.text.hint
  }
}));

const isSameDay = (start: Date, end: Date) => {
  if (!moment(start).isValid() || !moment(end).isValid()) return false;

  const startMoment = moment([
    start.getFullYear(),
    start.getMonth(),
    start.getDate()
  ]);
  const endMoment = moment([end.getFullYear(), end.getMonth(), end.getDate()]);

  return !startMoment.diff(endMoment);
};

function DateTimePickerField({
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
    disabled,
    component,
    value,
    required,
    inputFormat,
    formatValue,
    minDateTime,
    maxDateTime,
    timeSuggestion,
    labelDatePicker,
    labelTimePicker,
    nullable,
    timeFormat,
    description,
    ...restConfig
  } = config;
  const [field, meta, { setValue, setTouched }] = useField(name ?? 'datetime');
  const [selectedDate, setDate] = React.useState(
    nullable && !field.value ? null : moment(field.value ?? undefined).toDate()
  );
  const [selectedTime, setTime] = React.useState(
    nullable && !field.value ? null : moment(field.value ?? undefined).toDate()
  );
  const valueField =
    nullable && !field.value ? null : moment(field.value ?? undefined).toDate();

  const handleDateChange = (date: Date, context) => {
    const text = date;

    if (isString(context) && context?.length !== 10) return;

    if (moment.isMoment(date)) {
      date = date.toDate();
    }

    const isValidDate = moment(date).isValid();

    setDate(isValidDate ? new Date(date.toDateString()) : text);
  };

  const onBlurPicker = e => {
    const valueInput = e?.target?.value;
    const isValidDate = moment(valueInput).isValid();
    setDate(isValidDate ? moment(valueInput).toDate() : valueInput);
    setTouched(true);
  };

  const handleTimeChange = (time: Date) => {
    setTime(time);
  };

  React.useEffect(() => {
    const isValidDate = moment(selectedDate).isValid();
    const isValidTime = moment(selectedTime).isValid();

    if (!isValidDate && !isValidTime) {
      // set invalid data
      setValue(selectedDate || null);

      return;
    }

    let newDateTime = selectedDate;

    if (isValidTime) {
      const hour = moment(selectedTime).get('hour') * 60 * 60 * 1000;
      const minute = moment(selectedTime).get('minute') * 60 * 1000;
      const selectedDateStart = moment(selectedDate).startOf('day');
      newDateTime = new Date(selectedDateStart.valueOf() + hour + minute);
    }

    if (
      moment(field.value).seconds(0).milliseconds(0).toISOString() ===
      moment(newDateTime).toISOString()
    )
      return;

    setValue(moment(newDateTime).toISOString());
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedDate, selectedTime]);

  const haveError = Boolean(meta.error && (meta.touched || formik.submitCount));

  const hasMinTime = isSameDay(selectedDate, moment(minDateTime).toDate());
  const hasMaxTime = isSameDay(selectedDate, moment(maxDateTime).toDate());

  return (
    <Root data-testid={camelCase(`field ${name}`)}>
      <LocalizationProvider dateAdapter={AdapterMoment}>
        <Box sx={{ display: 'flex' }}>
          <Box>
            <DatePicker
              value={valueField}
              onChange={handleDateChange}
              label={labelDatePicker || label}
              minDate={minDateTime ? new Date(minDateTime) : null}
              maxDate={maxDateTime ? new Date(maxDateTime) : null}
              disabled={disabled || forceDisabled || formik.isSubmitting}
              dayOfWeekFormatter={(_day: string) => _day}
              onClose={() => setTouched(true)}
              renderInput={params => (
                <Text
                  {...params}
                  error={haveError}
                  required={required || forceRequired}
                  autoComplete="off"
                  data-testid={camelCase(`input ${name}`)}
                  onBlur={onBlurPicker}
                  inputProps={{
                    ...params?.inputProps,
                    'data-testid': camelCase(`inputDate ${name}`)
                  }}
                />
              )}
              {...restConfig}
            />
          </Box>
          <Box data-testid={camelCase('timePicker')} sx={{ paddingLeft: 2 }}>
            {timeSuggestion ? (
              <TimeSuggestionPicker
                {...config}
                hasMinTime={hasMinTime}
                hasMaxTime={hasMaxTime}
                value={valueField}
                handleChange={handleTimeChange}
                disabled={disabled || forceDisabled || formik.isSubmitting}
                timeFormat={timeFormat}
                error={haveError}
                required={required || forceRequired}
                setTouched={setTouched}
                name={name}
              />
            ) : (
              <TimePicker
                {...restConfig}
                ampmInClock
                minTime={hasMinTime ? new Date(minDateTime) : null}
                maxTime={hasMaxTime ? new Date(maxDateTime) : null}
                mask="__:__"
                minutesStep={1}
                disabled={disabled || forceDisabled || formik.isSubmitting}
                label={labelTimePicker || label}
                value={valueField}
                onChange={handleTimeChange}
                onClose={() => setTouched(true)}
                renderInput={params => (
                  <TextField
                    {...params}
                    required={required || forceRequired}
                    autoComplete="off"
                    error={haveError}
                    onBlur={() => setTouched(true)}
                    inputProps={{
                      ...params?.inputProps,
                      'data-testid': camelCase(`inputTime ${name}`)
                    }}
                  />
                )}
              />
            )}
          </Box>
        </Box>
        {description && <Description text={description} />}
      </LocalizationProvider>
      {haveError ? <ErrorMessage error={meta.error} /> : null}
    </Root>
  );
}

export default DateTimePickerField;

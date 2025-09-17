import { TextField, Autocomplete, Box, TextFieldProps } from '@mui/material';
import moment from 'moment';
import React from 'react';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import { FormElementShape } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { camelCase } from 'lodash';

const rangeTime = 15;

const generateTimePickerList = (timeStartDefault = 0, maxTime, timeFormat) => {
  const max = maxTime ? moment.duration(maxTime).asMinutes() : 24 * 60;
  const x = rangeTime; // minutes interval
  const times = []; // time array
  let tt = timeStartDefault; // start time

  // loop to increment the time and push results in array
  for (let i = 0; tt < max; i++) {
    const hh = Math.floor(tt / 60); // getting hours of day in 0-24 format
    const mm = tt % 60; // getting minutes of the hour in 0-55 format

    const ampm = hh >= 12 ? 'PM' : 'AM';
    const hours = hh % 12;
    const displayHours = hours ? hours : 12;

    times[i] = {
      label:
        timeFormat === 12
          ? `${`0${displayHours}`.slice(-2)}:${`0${mm}`.slice(-2)} ${ampm}`
          : `${`0${hh}`.slice(-2)}:${`0${mm}`.slice(-2)}`,
      value: `${`0${hh}`.slice(-2)}:${`0${mm}`.slice(-2)}`
    }; // pushing data in array in [00:00 - 12:00 AM/PM format]
    tt = tt + x;
  }

  return times;
};

const validateManualTime = (value: string) => {
  return moment(
    value,
    ['HH:mm', 'H:mm', 'HH:m', 'H:m', 'hh:mm A', 'hh:m A'],
    true
  ).isValid();
};

const convertManualTime = (value: string, timeFormat: number) => {
  return timeFormat === 12
    ? moment(value, ['hh:mm A', 'h:mm A']).format('hh:mm A')
    : moment(value, ['HH:mm', 'H:mm']).format('HH:mm');
};

const getMinutesStart = (time: Date) => {
  if (!time) return 0;

  const hour = moment(time).get('hour') * 60;
  const minute = moment(time).get('minute');
  const minuteQuarter = (hour + minute) / rangeTime;
  const ceilQuarter =
    minuteQuarter % 1 === 0 ? minuteQuarter + 1 : Math.ceil(minuteQuarter);

  return ceilQuarter * rangeTime;
};

export interface Props extends FormElementShape {}

function TimeSuggestionField(props: Props) {
  const {
    label,
    minDateTime,
    maxDateTime,
    hasMinTime,
    hasMaxTime,
    handleChange,
    value,
    disabled,
    labelTimePicker,
    timeFormat,
    error,
    required,
    setTouched,
    name
  } = props;
  const valueTimePicker = value ? convertManualTime(value, timeFormat) : '';
  const { i18n } = useGlobal();
  const minMinuteTimeStart = hasMinTime ? getMinutesStart(minDateTime) : 0;
  const optionsTimePicker = generateTimePickerList(
    minMinuteTimeStart,
    hasMaxTime ? maxDateTime : null,
    timeFormat
  );

  const handleParseTimeValue = data => {
    if (!data) return;

    const convertValue =
      timeFormat === 12
        ? moment(data, 'HH:mm A').toDate()
        : moment(data, 'HH:mm').toDate();
    handleChange(convertValue);
  };

  const handleTimePickerChange = (event, newValue) => {
    handleParseTimeValue(timeFormat === 12 ? newValue.label : newValue.value);
  };

  const handleBlurPickerTime = e => {
    const { value } = e.target;
    setTouched(true);

    if (value !== valueTimePicker) {
      handleParseTimeValue(
        validateManualTime(value)
          ? convertManualTime(value, timeFormat)
          : valueTimePicker
      );
    }
  };

  const renderInputTimePicker = (
    params: JSX.IntrinsicAttributes & TextFieldProps
  ) => {
    return (
      <TextField
        {...params}
        required={required}
        error={error}
        onBlur={handleBlurPickerTime}
        label={labelTimePicker || label}
        inputProps={{
          ...params.inputProps,
          'data-testid': camelCase(`inputTime ${name}`)
        }}
      />
    );
  };

  return (
    <Box
      sx={{
        '& .MuiAutocomplete-noOptions': {
          display: 'none !important'
        }
      }}
    >
      <Autocomplete
        sx={{
          minWidth: timeFormat === 12 ? '130px' : '100px',
          '& .MuiAutocomplete-popupIndicator': {
            transform: 'none !important'
          },
          '& .MuiInputLabel-formControl:not(.MuiInputLabel-shrink)': {
            maxWidth: 'calc(100% - 40px) !important'
          }
        }}
        disabled={disabled}
        value={valueTimePicker}
        id="time-suggestion-picker"
        options={optionsTimePicker}
        onChange={handleTimePickerChange}
        disableClearable
        renderInput={renderInputTimePicker}
        popupIcon={<AccessTimeIcon />}
        openText={i18n.formatMessage({ id: 'open' })}
        closeText={i18n.formatMessage({ id: 'close' })}
        forcePopupIcon
        ListboxProps={{ style: { maxHeight: '150px' } }}
        noOptionsText={i18n.formatMessage({ id: 'no_options' })}
      />
    </Box>
  );
}

export default TimeSuggestionField;

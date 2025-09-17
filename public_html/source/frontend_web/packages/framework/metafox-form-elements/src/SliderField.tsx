/**
 * @type: formElement
 * name: form.element.Slider
 */
import { Slider, FormControl, styled, Box, Typography } from '@mui/material';
import { useField } from 'formik';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { camelCase, get, set } from 'lodash';
import ErrorMessage from './ErrorMessage';
import Description from './Description';

const SliderStyled = styled(Slider, { name: 'slider' })(
  ({ theme, valueLabelDisplay }) => ({
    ...(valueLabelDisplay === 'on' && {
      height: '6px',
      marginTop: theme.spacing(4),
      '& .MuiSlider-valueLabel': {
        fontSize: theme.mixins.pxToRem(12),
        fontWeight: 'normal',
        backgroundColor: 'transparent',
        borderRadius: '50%',
        minWidth: '28px',
        minHeight: '28px',
        aspectRatio: '1',
        padding: theme.spacing(0.75),
        '&::before': {
          display: 'none'
        },
        '& *': {
          background: 'transparent',
          color: theme.palette.text.primary
        }
      },
      '& .MuiSlider-rail': {
        ...(theme.palette.mode === 'dark' && {
          backgroundColor: '#D9D9D9'
        })
      }
    })
  })
);

const TinyText = styled(Typography)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(12),
  fontWeight: theme.typography.fontWeightMedium,
  opacity: 0.38
}));

const SliderField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps) => {
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'SliderField'
  );
  const getAriaValueText = (value: number): string => `${value} years`;
  const {
    sxFieldWrapper,
    fullWidth,
    margin,
    disabled,
    required,
    valueNames,
    min,
    max,
    description,
    valueLabelDisplay = 'auto',
    showLabelMinMax = false,
    ...rest
  } = config;
  const isMutiple = valueNames?.length;
  const haveError = Boolean(meta.error && (meta.touched || formik.submitCount));

  const normalizeValue = React.useCallback(
    data => {
      return isMutiple ? valueNames.map(x => get(data, x)) : data;
    },
    [isMutiple, valueNames]
  );

  const toFieldValue = React.useCallback(
    data => {
      if (!isMutiple) return data;

      const objData = {};
      valueNames.forEach((x, index) => set(objData, x, data[index]));

      return objData;
    },
    [isMutiple, valueNames]
  );

  const value = normalizeValue(field.value);

  const handleChange = (e, newValue) => {
    setTouched(true);
    setValue(toFieldValue(newValue));
  };

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
    >
      <SliderStyled
        {...field}
        value={value}
        onChange={handleChange}
        data-testid={camelCase(`field ${name}`)}
        getAriaValueText={getAriaValueText}
        valueLabelDisplay={valueLabelDisplay}
        required={required || forceRequired}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        min={min}
        max={max}
        {...rest}
      />
      {showLabelMinMax && min && max ? (
        <Box
          sx={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            mx: -0.75
          }}
        >
          <TinyText>{min}</TinyText>
          <TinyText>{max}</TinyText>
        </Box>
      ) : null}
      {haveError ? <ErrorMessage error={meta.error} /> : null}
      {description ? <Description text={description} /> : null}
    </FormControl>
  );
};

export default SliderField;

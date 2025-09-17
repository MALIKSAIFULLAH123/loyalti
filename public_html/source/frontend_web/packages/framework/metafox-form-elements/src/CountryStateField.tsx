/**
 * @type: formElement
 * name: form.element.CountryState
 * chunkName: formExtras
 */
import { OptionItemShape as ItemShape, useGlobal } from '@metafox/framework';
import { Autocomplete, FormControl, TextField, Box } from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';

const SelectField = ({
  name,
  config,
  disabled: forceDisabled,
  required: forceRequired,
  formik
}: FormFieldProps) => {
  const {
    margin = 'normal',
    fieldLabel = 'label',
    fieldValue = 'value',
    options,
    disabled,
    suboptions: stateOptions,
    countryFieldName,
    stateFieldName,
    cityFieldName = 'country_city_code',
    required,
    label,
    size,
    sx,
    sxFieldWrapper,
    fullWidth,
    inline
  } = config;

  const { i18n } = useGlobal();

  const [
    countryField,
    metaCountryField,
    { setValue: setCountryValue, setTouched: setCountryTouched }
  ] = useField(countryFieldName);
  const [stateField, , { setValue: setStateValue }] = useField(stateFieldName);

  const countryValue = React.useMemo(() => {
    return countryField.value
      ? options.find(item => item?.value === countryField.value)
      : null;
  }, [countryField.value, options]);

  const stateValue = React.useMemo(() => {
    if (!countryField.value) return null;

    return (
      stateOptions[countryField.value]?.find(
        // eslint-disable-next-line eqeqeq
        item => item?.value == stateField.value
      ) || null
    );
  }, [stateField.value, stateOptions, countryField]);

  const stateOption = stateOptions[countryField.value];

  const handleChangeCountry = value => {
    if (!metaCountryField.touched) {
      setCountryTouched(true);
    }

    setCountryValue(value?.[fieldValue] || null);
    setStateValue(null);

    if (cityFieldName) {
      formik.setFieldValue(cityFieldName, null);
    }
  };

  const handleStateChange = value => {
    setStateValue(value?.[fieldValue] || null);

    if (cityFieldName) {
      formik.setFieldValue(cityFieldName, null);
    }
  };

  const getOptionLabel = (option: ItemShape): string => {
    return option[fieldLabel];
  };

  const handleInputBlur = () => {
    setCountryTouched(true);
  };

  const haveErrorCountryField: boolean = !!(
    metaCountryField.error &&
    (metaCountryField.touched || formik.submitCount)
  );

  if (inline) {
    return (
      <>
        <FormControl
          margin={margin}
          fullWidth={fullWidth}
          data-testid={camelCase(`field ${name}`)}
          sx={sxFieldWrapper}
        >
          <Autocomplete<ItemShape, false>
            sx={sx}
            openOnFocus
            autoHighlight
            value={countryValue}
            disabled={disabled || forceDisabled || formik.isSubmitting}
            onChange={(evt, newValue) => handleChangeCountry(newValue)}
            onBlur={countryField.onBlur}
            id={camelCase(`select-${name}`)}
            multiple={false}
            options={options}
            noOptionsText={i18n.formatMessage({ id: 'no_options' })}
            getOptionLabel={getOptionLabel}
            renderInput={params => (
              <TextField
                data-testid={camelCase(`field ${name}`)}
                label={label}
                required={required || forceRequired}
                error={haveErrorCountryField}
                helperText={
                  haveErrorCountryField ? metaCountryField.error : null
                }
                onBlur={handleInputBlur}
                {...params}
                size={size}
              />
            )}
          />
        </FormControl>

        {stateOption?.length > 0 && (
          <FormControl
            margin={margin}
            fullWidth={fullWidth}
            data-testid={camelCase(`field ${stateFieldName}`)}
            sx={sxFieldWrapper}
          >
            <Box>
              <Autocomplete<ItemShape, false>
                sx={sx}
                openOnFocus
                autoHighlight
                disabled={disabled || forceDisabled || formik.isSubmitting}
                value={stateValue}
                onChange={(evt, newValue) => handleStateChange(newValue)}
                onBlur={stateField.onBlur}
                id={`select-${name}`}
                multiple={false}
                getOptionLabel={getOptionLabel}
                options={stateOption}
                noOptionsText={i18n.formatMessage({ id: 'no_options' })}
                renderInput={params => (
                  <TextField
                    label={i18n.formatMessage({ id: 'state' })}
                    {...params}
                    size={size}
                  />
                )}
              />
            </Box>
          </FormControl>
        )}
      </>
    );
  }

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
    >
      <Autocomplete<ItemShape, false>
        sx={sx}
        openOnFocus
        autoHighlight
        value={countryValue}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        onChange={(evt, newValue) => handleChangeCountry(newValue)}
        onBlur={countryField.onBlur}
        id={camelCase(`select-${name}`)}
        multiple={false}
        options={options}
        noOptionsText={i18n.formatMessage({ id: 'no_options' })}
        getOptionLabel={getOptionLabel}
        renderInput={params => (
          <TextField
            data-testid={camelCase(`field ${name}`)}
            label={label}
            required={required || forceRequired}
            error={haveErrorCountryField}
            helperText={haveErrorCountryField ? metaCountryField.error : null}
            onBlur={handleInputBlur}
            {...params}
            size={size}
          />
        )}
      />
      {stateOption?.length > 0 && (
        <Box mt={2}>
          <Autocomplete<ItemShape, false>
            sx={sx}
            openOnFocus
            autoHighlight
            disabled={disabled || forceDisabled || formik.isSubmitting}
            value={stateValue}
            onChange={(evt, newValue) => handleStateChange(newValue)}
            onBlur={stateField.onBlur}
            id={`select-${name}`}
            multiple={false}
            getOptionLabel={getOptionLabel}
            options={stateOption}
            noOptionsText={i18n.formatMessage({ id: 'no_options' })}
            renderInput={params => (
              <TextField
                label={i18n.formatMessage({ id: 'state' })}
                {...params}
                size={size}
              />
            )}
          />
        </Box>
      )}
    </FormControl>
  );
};

export default SelectField;

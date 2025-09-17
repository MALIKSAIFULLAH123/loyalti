/**
 * @type: formElement
 * name: form.element.CheckboxGroup
 */
import { FormFieldProps } from '@metafox/form';
import { styled, Typography, Link, Box, FormLabel } from '@mui/material';
import Checkbox from '@mui/material/Checkbox';
import FormControl from '@mui/material/FormControl';
import FormControlLabel from '@mui/material/FormControlLabel';
import FormGroup from '@mui/material/FormGroup';
import FormHelperText from '@mui/material/FormHelperText';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import { useGlobal } from '@metafox/framework';

const StyledForm = styled(Box, {
  name: 'StyledForm'
})(({ theme }) => ({
  display: 'inline-block'
}));
const DescriptionStyled = styled(Typography)(({ theme }) => ({
  marginLeft: theme.spacing(3.75)
}));

const CheckboxGroupField = ({
  config,
  name,
  disabled: forceDisabled,
  required: forceRequired,
  formik
}: FormFieldProps) => {
  const { i18n } = useGlobal();
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'CheckboxGroupField'
  );
  const {
    label,
    description,
    descriptionConfig,
    selectAllToggle = false,
    options,
    variant,
    margin,
    fullWidth,
    size,
    disabled,
    hasFormOrder = false,
    order,
    required,
    titleConfig = {}
  } = config;

  const value = field.value || [];

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  const orderLabel = hasFormOrder && order ? `${order}. ` : null;
  const optionsFilter = options.filter(item => !item.disabled);
  const isSelectAll = optionsFilter.length > value.length;
  const toggleAll = React.useCallback(() => {
    const newValues = isSelectAll ? optionsFilter.map(item => item.value) : [];
    setValue(newValues);
  }, [setValue, isSelectAll, optionsFilter]);

  const handleChange = (e, optionValue) => {
    setTouched(true);

    // field.onChange not working on value type number
    if (e.target.checked) {
      setValue([...value, optionValue]);
    } else {
      setValue(value.filter(x => x !== optionValue));
    }
  };

  return (
    <FormControl
      component="fieldset"
      fullWidth={fullWidth}
      margin={margin}
      size={size}
      error={haveError}
      variant={variant as any}
      data-testid={camelCase(`field ${name}`)}
    >
      {label ? (
        <FormLabel
          focused={false}
          required={required || forceRequired}
          {...titleConfig}
        >
          {orderLabel}
          {label}
        </FormLabel>
      ) : null}
      {description ? (
        <Typography
          my={1}
          color="text.secondary"
          variant="body2"
          {...descriptionConfig}
        >
          {description}
        </Typography>
      ) : null}
      {selectAllToggle ? (
        <Box my={1}>
          <Link
            component="span"
            color="primary"
            variant="body2"
            onClick={toggleAll}
            disabled={disabled || forceDisabled || formik.isSubmitting}
          >
            {i18n.formatMessage({
              id: isSelectAll ? 'select_all' : 'deselect_all'
            })}
          </Link>
        </Box>
      ) : null}
      <FormGroup>
        {options
          ? options.map((item, index) => (
              <StyledForm key={index.toString()}>
                <FormControlLabel
                  control={
                    <Checkbox
                      checked={value.some(
                        itemValue =>
                          item.value.toString() === itemValue.toString()
                      )}
                      onChange={e => handleChange(e, item.value)}
                      name={name}
                      data-testid={camelCase(`input ${name}`)}
                      disabled={
                        item?.disabled ||
                        disabled ||
                        forceDisabled ||
                        formik.isSubmitting
                      }
                    />
                  }
                  label={item.label}
                  value={item.value}
                  disabled={
                    item?.disabled ||
                    disabled ||
                    forceDisabled ||
                    formik.isSubmitting
                  }
                />
                {item?.description && (
                  <DescriptionStyled
                    variant="body2"
                    color={
                      disabled ||
                      item.disabled ||
                      forceDisabled ||
                      formik.isSubmitting
                        ? 'text.disabled'
                        : 'text.secondary'
                    }
                  >
                    {item.description}
                  </DescriptionStyled>
                )}
              </StyledForm>
            ))
          : null}
      </FormGroup>
      {haveError ? <FormHelperText>{meta.error}</FormHelperText> : null}
    </FormControl>
  );
};

export default CheckboxGroupField;

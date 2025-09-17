/**
 * @type: formElement
 * name: form.element.TypeCategory
 */
import { FormFieldProps } from '@metafox/form';
import isString from '@metafox/scrollbars/utils/isString';
import { Autocomplete, Box, FormControl, TextField } from '@mui/material';
import { styled } from '@mui/material/styles';
import { useField } from 'formik';
import { camelCase, isObject } from 'lodash';
import React, { KeyboardEventHandler } from 'react';
import Description from '../Description';

interface ItemShape {
  label: string;
  value: string;
  typeVale: number;
  typeLabel: string;
  categoryValue: number;
  categoryLabel: string;
}

interface OptionShape {
  id: number;
  name: string;
  categories?: OptionShape[];
}

const StyledOption = styled(Box, {
  name: 'MuiOption',
  slot: 'Root'
})({
  height: 48,
  alignItems: 'center'
});

const renderOption = (props: Record<string, any>, option: ItemShape) => {
  return (
    <StyledOption component="li" {...props}>
      {option.label}
    </StyledOption>
  );
};

const createId = (parent: number, child: number): string =>
  `<${parent},${child}>`;

function TypeCategoryField({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    variant,
    label,
    size = 'medium',
    margin = 'normal',
    color,
    disabled,
    placeholder,
    description,
    options: options2,
    required,
    fullWidth = true,
    multiple,
    sx
  } = config;

  const [categoryField, meta, { setValue: setCategory, setTouched }] = useField(
    name ?? 'type_id'
  );

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  const options = React.useMemo(() => {
    const response: ItemShape[] = [];

    if (!options2?.length) return response;

    options2.forEach((type: OptionShape) => {
      type.categories.forEach(category => {
        response.push({
          value: createId(type.id, category.id),
          label: category.name,
          typeValue: type.id,
          typeLabel: type.name,
          categoryValue: category.id,
          categoryLabel: category.name
        });
      });
    });

    return response;
  }, [options2]);

  const defaultValue = React.useMemo(() => {
    const found = options.find(
      item => item.categoryValue === categoryField.value
    );

    return found;
  }, [categoryField.value, options]);

  // prevent enter
  const handleKeyDown: KeyboardEventHandler<HTMLDivElement> = evt => {
    if (evt.keyCode === 13) {
      evt.preventDefault();
    }
  };

  const handleChange = (newValue: ItemShape | string) => {
    let found: ItemShape;

    if (newValue && isString(newValue)) {
      found = options.find(x => x.value === newValue);
    } else if (isObject(newValue)) {
      found = newValue;
    }

    if (found) {
      setCategory(found.categoryValue);
    } else {
      setCategory(undefined);
    }
  };

  const onBlur = () => {
    if (!meta?.touched) {
      setTouched(true);
    }

    setCategory(categoryField.value);
  };

  return (
    <FormControl
      variant={variant as any}
      margin={margin}
      fullWidth={fullWidth}
      size={size}
      data-testid={camelCase(`field ${name}`)}
    >
      <Autocomplete<ItemShape, false>
        id={`select-${name}`}
        openOnFocus
        onBlur={onBlur}
        options={options}
        fullWidth={fullWidth}
        groupBy={option => option.typeLabel}
        getOptionLabel={option => option?.label || ''}
        color={color}
        defaultValue={defaultValue}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        onChange={(evt, newValue) => handleChange(newValue)}
        size={size}
        sx={sx}
        multiple={multiple}
        renderOption={renderOption}
        renderInput={params => (
          <TextField
            {...params}
            label={label}
            onKeyDown={handleKeyDown}
            fullWidth={fullWidth}
            placeholder={placeholder}
            required={required}
            error={haveError}
            variant={variant as any}
            size={size}
            sx={sx}
            helperText={
              haveError && meta.error ? (
                meta.error
              ) : description ? (
                <Description text={description} />
              ) : null
            }
          />
        )}
      />
    </FormControl>
  );
}

export default TypeCategoryField;

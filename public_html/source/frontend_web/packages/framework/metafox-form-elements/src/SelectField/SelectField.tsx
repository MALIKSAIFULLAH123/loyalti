/**
 * @type: formElement
 * name: form.element.Select
 * chunkName: formElement
 */
import { FormFieldProps } from '@metafox/form';
import { toOptions } from '@metafox/utils';
import {
  Autocomplete,
  Box,
  Chip,
  FormControl,
  FormHelperText,
  Typography,
  TextField
} from '@mui/material';
import { createFilterOptions } from '@mui/material/Autocomplete';
import { useField } from 'formik';
import {
  camelCase,
  get,
  isArray,
  range,
  intersectionWith,
  isEqual
} from 'lodash';
import React, { KeyboardEventHandler } from 'react';
import { styled } from '@mui/material/styles';
import { useGlobal } from '@metafox/framework';
import { useBlock } from '@metafox/layout';

interface ItemShape {
  label: string;
  value: number;
  disabled?: boolean;
  prefix?: string;
  options?: ItemShape[];
  parentLabel?: string;
  parentValue?: number;
  childLabel?: string;
  childValue?: number;
}

const LineIndent = styled('span')(({ theme }) => ({
  paddingLeft: theme.spacing(1.5)
}));

const SelectField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps) => {
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'SelectField'
  );
  const { slotName } = useBlock();
  const {
    label,
    margin,
    disabled,
    fullWidth,
    hiddenLabel,
    required,
    variant,
    size,
    sx,
    sxFieldWrapper,
    description,
    defaultValue = '',
    value_type,
    multiple = false,
    autoComplete = 'off',
    options: options2 = [],
    subOptionPrefix = ' • ',
    suboptions: subOptions = {},
    allowParent = true, // allow choose parents
    subOptionKey = 'options',
    labelProp = 'label',
    valueProp = 'value',
    relatedFieldName,
    optionRelatedMapping,
    alwaysShow,
    disableClearable,
    disablePortal: disablePortalConfig
  } = config;
  const [fieldRelated] = useField(relatedFieldName ?? `${name}_child`);
  const [excludesOption, setExcludesOption] = React.useState([]);
  const [optionRelatedState, setOptionRelatedState] = React.useState([]);
  const { i18n, useIsMobile } = useGlobal();
  const isMobile = useIsMobile();
  const disablePortal =
    disablePortalConfig ?? (slotName === 'side' && !isMobile);

  React.useEffect(() => {
    if (!relatedFieldName) return;

    if (optionRelatedMapping) {
      // case mapping option with related field
      if (isArray(fieldRelated?.value)) return;

      const optionRelated = optionRelatedMapping[fieldRelated?.value] || [];
      setOptionRelatedState(optionRelated);

      if (optionRelated.some(x => x.value === field.value)) {
        return;
      }

      const valueDefault = optionRelated.find(item => item?.is_default);

      setValue(valueDefault?.value || null);

      return;
    }

    // case exclude value when use same option with other field
    if (isArray(fieldRelated?.value)) {
      setExcludesOption(fieldRelated?.value);
    } else {
      setExcludesOption([fieldRelated?.value]);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [relatedFieldName, fieldRelated?.value]);

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );
  const filterOptions = createFilterOptions<any>({
    stringify: ({ label }) => `${label}`
  });
  const isOptionEqualToValue = (option: ItemShape, value: ItemShape): boolean =>
    // eslint-disable-next-line eqeqeq
    get(option, 'value') == get(value, 'value');

  const renderOption = React.useCallback(
    (props: Record<string, any>, option: ItemShape) => {
      // add key value because fix for case duplicate same name label on options
      let levelIndent = option.prefix ? 1 : 0;

      if (option?.level) {
        levelIndent = option?.level - 1;
      }

      return (
        <Box
          data-value={option?.value}
          component="li"
          {...props}
          key={`${option?.value}${option?.label}`}
        >
          {levelIndent
            ? range(0, levelIndent).map((x, index) => (
                <LineIndent key={`indent${index}`} />
              ))
            : ''}
          {option.label}
        </Box>
      );
    },
    []
  );

  const getOptionLabel = (option: ItemShape): string => {
    return get(option, 'label') ?? '';
  };

  const options = React.useMemo(() => {
    let optionData = [];

    if (isArray(options2)) {
      optionData =
        optionRelatedState.length && optionRelatedMapping
          ? optionRelatedState
          : options2.filter(x => !excludesOption.includes(x.value));
    }

    if (isArray(options2))
      return toOptions(
        optionData,
        subOptions,
        allowParent,
        subOptionKey,
        subOptionPrefix,
        0,
        valueProp,
        labelProp
      );

    return [];
  }, [
    optionRelatedMapping,
    optionRelatedState,
    options2,
    subOptions,
    subOptionKey,
    allowParent,
    subOptionPrefix,
    valueProp,
    labelProp,
    excludesOption
  ]);

  const { optionsActive, optionsUnactive } = options.reduce(
    (memo, x) => {
      if (x.is_active !== undefined) {
        if (x.is_active) {
          memo.optionsActive.push(x);
        } else {
          memo.optionsUnactive.push(x);
        }
      } else {
        memo.optionsActive.push(x);
      }

      return memo;
    },
    { optionsActive: [], optionsUnactive: [] }
  );

  const optionMap: Record<string, ItemShape> = React.useMemo(() => {
    const data = {};

    options.forEach(option => {
      data[option.value] = option;
    });

    return data;
  }, [options]);

  const indexValue = React.useMemo(
    () => options2.findIndex(option => option.value === field.value),
    [field.value, options2]
  );

  const handleChangeMultiple = newValue => {
    if (isArray(newValue)) {
      setValue(newValue.map(option => option['value']));
    } else {
      setValue([]);
    }
  };

  const handleChangeSingle = React.useCallback(
    newValue => {
      if (newValue) {
        const vx = newValue['value'];
        setValue(value_type === 'array' ? [vx] : vx);
      } else {
        setValue(value_type === 'array' ? [] : null);
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const handleChange = data => {
    if (!meta?.touched) {
      setTouched(true);
    }

    return multiple ? handleChangeMultiple(data) : handleChangeSingle(data);
  };

  // validate use value
  const optionValue: ItemShape[] | ItemShape = React.useMemo(() => {
    if (multiple) {
      if (isArray(field.value)) {
        return field.value
          .map(value => optionMap[value])
          .filter(x => x !== undefined);
      } else {
        return [];
      }
    } else {
      // single
      const vx: string = isArray(field.value) ? field.value[0] : field.value;

      if (vx !== undefined) {
        return optionMap[vx];
      }
    }
  }, [field.value, multiple, optionMap]);

  const optionDefaultValue: ItemShape[] | ItemShape = React.useMemo(() => {
    if (multiple) {
      if (isArray(defaultValue)) {
        return defaultValue
          .map(value => optionMap[value])
          .filter(x => x !== undefined);
      } else {
        return [];
      }
    } else {
      // single
      const vx: string = isArray(defaultValue) ? defaultValue[0] : defaultValue;

      if (vx !== undefined) {
        return optionMap[vx];
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!options.length && !alwaysShow) return null;

  const getOptionDisabled = option => option.disabled;

  // prevent enter
  const handleKeyDown: KeyboardEventHandler<HTMLDivElement> = evt => {
    if (evt.keyCode === 13) {
      evt.preventDefault();
    }
  };

  const handleBlur = e => {
    field.onBlur(e);
    setTouched(true);
  };

  const renderInput = params => {
    return (
      <TextField
        {...params}
        label={label}
        variant={variant}
        size={size}
        fullWidth
        onKeyDown={handleKeyDown}
        error={haveError}
        required={required || forceRequired}
        inputProps={{
          ...params.inputProps,
          'data-testid': camelCase(`input ${name}`),
          form: {
            autoComplete
          }
        }}
        sx={sx}
      />
    );
  };

  const renderTags = (tagValue: ItemShape[], getTagProps: Function) => {
    return tagValue.map((option, index) => (
      <Chip
        {...getTagProps({ index })}
        key={option?.value}
        label={option?.label}
        sx={{
          opacity: optionsUnactive.map(x => x.value).includes(option?.value)
            ? 0.5
            : undefined
        }}
      />
    ));
  };

  const valuesUnactive = intersectionWith(
    isArray(optionValue) ? optionValue : [optionValue],
    optionsUnactive,
    isEqual
  );

  const totalValuesUnactive = valuesUnactive.length;

  return (
    <FormControl
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
      <Autocomplete
        clearText={i18n.formatMessage({ id: 'clear' })}
        openText={i18n.formatMessage({ id: 'open' })}
        closeText={i18n.formatMessage({ id: 'close' })}
        filterOptions={filterOptions}
        disableClearable={disableClearable || required}
        openOnFocus
        value={optionValue ?? optionDefaultValue ?? defaultValue}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        defaultValue={optionDefaultValue ?? defaultValue}
        onChange={(_, newValue) => handleChange(newValue)}
        onBlur={handleBlur}
        id={`select-${name}`}
        sx={sx}
        isOptionEqualToValue={isOptionEqualToValue}
        options={optionsActive}
        noOptionsText={i18n.formatMessage({ id: 'no_options' })}
        getOptionDisabled={getOptionDisabled}
        multiple={multiple}
        getOptionLabel={getOptionLabel}
        autoHighlight
        renderInput={renderInput}
        renderOption={renderOption}
        renderTags={renderTags}
        ListboxProps={{ 'data-testid': camelCase(`menu ${name}`) }}
        data-testid={camelCase(`input ${name}`)}
        disablePortal={disablePortal}
      />
      {!description && options2[indexValue]?.description && (
        <Typography sx={{ fontSize: '13px' }} color="text.hint" mt={1}>
          {options2[indexValue]?.description}
        </Typography>
      )}
      {description && (
        <Typography sx={{ fontSize: '13px' }} color="text.hint" mt={1}>
          {description}
        </Typography>
      )}
      {totalValuesUnactive > 0 && (
        <Typography mt={1} color="warning.main" variant="body2">
          {i18n.formatMessage(
            { id: 'the_following_option_s_have_been_deactivated' },
            {
              values: valuesUnactive.map(x => x.label).join(', '),
              total: totalValuesUnactive
            }
          )}
        </Typography>
      )}
      {haveError && <FormHelperText>{meta?.error}</FormHelperText>}
    </FormControl>
  );
};

export default SelectField;

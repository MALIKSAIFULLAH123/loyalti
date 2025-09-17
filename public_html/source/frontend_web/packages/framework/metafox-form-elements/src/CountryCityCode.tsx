/**
 * @type: formElement
 * name: form.element.CountryCityCode
 * chunkName: formExtras
 */
import { FormFieldProps } from '@metafox/form';
import { useGlobal, useSuggestions, usePrevious } from '@metafox/framework';
import {
  Autocomplete,
  Box,
  CircularProgress,
  FormControl,
  FormHelperText,
  TextField
} from '@mui/material';
import { useField, useFormikContext } from 'formik';
import {
  camelCase,
  get,
  omit,
  isObject,
  debounce,
  omitBy,
  isNil,
  isEqual
} from 'lodash';
import React from 'react';

type ItemShape = {
  label: string;
  value: string;
};

const LIMIT_RENDER_OPTIONS = 20;

const isOptionEqualToValue = (option: ItemShape, value: ItemShape): boolean => {
  // eslint-disable-next-line eqeqeq
  return get(option, 'value') == get(value, 'value');
};

const renderOption = (props: Record<string, any>, option: ItemShape) => {
  // add key value because fix for case duplicate same name label on options

  return (
    <Box
      data-value={option?.value}
      component="li"
      {...props}
      key={`${option?.value}${option?.label}`}
    >
      {option.label}
    </Box>
  );
};

const CountryCityCodeField = ({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) => {
  const {
    variant = 'outlined',
    label,
    size = 'medium',
    margin = 'normal',
    disabled,
    placeholder,
    required,
    fullWidth = true,
    search_endpoint,
    search_params,
    sxFieldWrapper
  } = config;
  const { values } = useFormikContext();
  const { compactData } = useGlobal();
  const [inputValue, setInputValue] = React.useState('');
  const initRef = React.useRef(false);
  const init = initRef.current;
  const [key, setKey] = React.useState(1);
  const [field, meta, { setValue }] = useField(name || 'autoComplete');
  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );
  const filterParams = React.useMemo(() => {
    const data = isObject(values) ? values : {};

    // support API remove [name] city_code after firstInit
    // when init, Options is empty and need city_code to fetch list has option selected
    // after that just search by text input(not use city_code)
    return omitBy(
      compactData(
        search_params,
        omit(data, ['q', initRef.current ? name : undefined])
      ),
      isNil
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search_params, values, inputValue]);
  const prevFilterParams = usePrevious(filterParams);

  const [data, handleChanged] = useSuggestions<ItemShape>({
    apiUrl: search_endpoint,
    initialParams: filterParams
  });

  const onBlur = e => {
    field.onBlur(e);

    if (!inputValue) {
      setValue(null);
    }
  };

  const debounced = React.useMemo(() => {
    const getSuggestions = () => {
      handleChanged(inputValue, filterParams);
    };

    return debounce(getSuggestions, 200);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [inputValue, filterParams]);

  const handleFocus = () => {
    debounced();
  };

  React.useEffect(() => {
    if (!init) return;

    debounced();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [inputValue]);

  React.useEffect(() => {
    if (!init || isEqual(filterParams, prevFilterParams)) return;

    debounced();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filterParams]);

  React.useEffect(() => {
    if (!field.value) {
      setInputValue('');
    }
  }, [field.value]);

  React.useEffect(() => {
    // Init one time to get default data (support form static)
    if (init) return;

    const optionSelected =
      field.value && !data?.loading
        ? // eslint-disable-next-line eqeqeq
          data.items.find(i => i.value == field.value)
        : '';

    if (optionSelected) {
      initRef.current = true;
      setInputValue(optionSelected?.label);

      return;
    }

    if (!data?.loading || data?.items?.length > 0) {
      setKey(Math.random());
      initRef.current = true;
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data?.items, init]);

  if (!init)
    return (
      <Box
        sx={{
          textAlign: 'center',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center'
        }}
      >
        <CircularProgress color="inherit" size={20} />
      </Box>
    );

  if (!inputValue && !data.items?.length && !data.loading) return null;

  return (
    <FormControl
      variant={variant as any}
      margin={margin}
      fullWidth={fullWidth}
      required={required}
      size={size}
      data-testid={camelCase(`field ${name}`)}
      error={haveError}
      sx={sxFieldWrapper}
    >
      <Autocomplete
        freeSolo
        key={key}
        size={size}
        autoHighlight
        openOnFocus
        onBlur={onBlur}
        onFocus={handleFocus}
        onChange={(evt, newValue) => {
          setValue(newValue?.value);
        }}
        renderOption={renderOption}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        placeholder={placeholder}
        isOptionEqualToValue={isOptionEqualToValue}
        options={data.items?.slice(0, LIMIT_RENDER_OPTIONS)}
        loading={data.loading}
        loadingText={
          <Box sx={{ display: 'flex', justifyContent: 'center' }}>
            <CircularProgress />
          </Box>
        }
        onInputChange={(event, newInputValue) => {
          setInputValue(newInputValue);
        }}
        inputValue={inputValue || ''}
        renderInput={params => (
          <TextField
            {...params}
            label={label}
            data-testid={camelCase(`field ${name}`)}
            error={haveError}
          />
        )}
      />
      {haveError && <FormHelperText>{meta?.error}</FormHelperText>}
    </FormControl>
  );
};

export default CountryCityCodeField;

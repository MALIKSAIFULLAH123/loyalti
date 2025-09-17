/**
 * @type: formElement
 * name: form.element.FriendPicker
 * chunkName: formExtras
 */
import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { UserItemShape } from '@metafox/user';
import { Autocomplete, styled, TextField } from '@mui/material';
import { useField } from 'formik';
import { camelCase, debounce, isArray } from 'lodash';
import React, { KeyboardEventHandler, useEffect } from 'react';
import Option from './OptionCustomAutoComplete';

export const StyledTextField = styled(TextField, { name: 'StyledTextField' })(
  ({ theme }) => ({
    '& input::placeholder': {
      color: theme.palette.text.secondary
    },
    '& .MuiInputLabel-outlined': {
      transform: 'translate(16px, 16px)'
    },
    '& .MuiInputLabel-outlined.MuiInputLabel-shrink': {
      transform: 'translate(14px, -6px) scale(0.8667)',
      backgroundColor: theme.palette.background.paper,
      paddingRight: theme.spacing(0.5)
    },
    '& .MuiOutlinedInput-root': {
      minHeight: theme.spacing(6),
      borderRadius: theme.spacing(0.5),
      paddingTop: theme.spacing(1),
      paddingBottom: theme.spacing(1)
    },
    '& .MuiFormHelperText-root': {
      color: theme.palette.grey[500]
    },
    '& .MuiOutlinedInput-input': {
      padding: theme.spacing(0, 2),
      height: 13
    },
    '& .MuiAutocomplete-endAdornment': {
      top: 'auto'
    }
  })
);

const FriendPickerFieldWrapper = styled('div', { name: 'FriendPickerField' })(
  ({ theme }) => ({
    padding: theme.spacing(2, 0, 1)
  })
);

const FriendPickerField = ({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) => {
  const {
    label,
    placeholder,
    multiple,
    disabled,
    api_endpoint = '/friend',
    apiParams = { limit: 10 },
    resetWhenUnmount = false,
    noOptionsText
  } = config;

  const query = React.useRef<string>('');

  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'FriendPickerField'
  );
  const { apiClient, i18n } = useGlobal();

  const [items, setItems] = React.useState<UserItemShape[]>([]);
  const [selectedValue, setSelectedValue] = React.useState(
    multiple ? [] : null
  );
  const [loading, setLoading] = React.useState<boolean>(false);

  const debouncedSuggestion = React.useMemo(() => {
    const getSuggestions = () => {
      setLoading(true);
      apiClient
        .get(api_endpoint, {
          params: { ...apiParams, q: query.current || undefined }
        })
        .then(result => result.data.data)
        .then(items => isArray(items) && setItems(items))
        .finally(() => {
          setLoading(false);
        });
    };

    return debounce(getSuggestions, 200);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [apiClient, query]);

  const handleInputChange = (e: React.ChangeEvent<{}>, value: string) => {
    query.current = value;

    debouncedSuggestion();
  };

  // prevent enter
  const handleKeyDown: KeyboardEventHandler<HTMLDivElement> = evt => {
    if (evt.keyCode === 13) {
      evt.preventDefault();
    }
  };

  const handleChange = (
    _: React.ChangeEvent<{}>,
    values: UserItemShape & UserItemShape[]
  ) => {
    if (!meta?.touched) {
      setTouched(true);
    }

    if (!values) return;

    setSelectedValue(values);

    setValue(values);
  };

  useEffect(() => {
    debouncedSuggestion();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (!field.value) return;

    setSelectedValue(field.value);

    return () => {
      if (resetWhenUnmount) {
        setValue({});
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <FriendPickerFieldWrapper>
      <Autocomplete
        clearText={i18n.formatMessage({ id: 'clear' })}
        closeText={i18n.formatMessage({ id: 'close' })}
        openText={i18n.formatMessage({ id: 'open' })}
        multiple={multiple}
        id={field.name}
        options={items}
        loading={loading}
        loadingText={i18n.formatMessage({ id: 'loading_dots' })}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        value={selectedValue}
        onChange={handleChange}
        onInputChange={handleInputChange}
        data-testid={camelCase(`field ${name}`)}
        disableCloseOnSelect={multiple}
        limitTags={3}
        isOptionEqualToValue={(option, value) => option?.id === value?.id}
        getOptionLabel={option => option?.full_name ?? option?.title ?? ''}
        filterSelectedOptions={multiple}
        noOptionsText={
          noOptionsText || i18n.formatMessage({ id: 'no_friends_found' })
        }
        renderOption={(props, value) => (
          <Option key={value.id} props={props} value={value} />
        )}
        renderInput={params => (
          <StyledTextField
            {...params}
            onKeyDown={handleKeyDown}
            variant="outlined"
            label={label}
            data-testid={camelCase(`input ${name}`)}
            placeholder={multiple && selectedValue?.length ? '' : placeholder}
          />
        )}
      />
    </FriendPickerFieldWrapper>
  );
};

export default FriendPickerField;

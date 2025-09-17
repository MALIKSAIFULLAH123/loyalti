/**
 * @type: formElement
 * name: form.element.Tags
 * chunkName: formExtras
 */
import { FormFieldProps } from '@metafox/form';
import { useGlobal, useSuggestions } from '@metafox/framework';
import {
  Autocomplete,
  ChipProps,
  FormControl,
  TextField,
  Typography,
  styled
} from '@mui/material';
import { useField } from 'formik';
import { camelCase, isArray, uniq } from 'lodash';
import React from 'react';
import ErrorMessage from '@metafox/form-elements/ErrorMessage';

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& p, input::placeholder': {
    color: theme.palette.text.hint
  }
}));

type ItemShape = string;

const chipProps: ChipProps = { size: 'small', variant: 'outlined' };

const formatInputData = tagsArray => {
  const notEmptyTagFilter = tagsArray.filter(item => item.trim().length > 0);

  return notEmptyTagFilter;
};

function TagsField({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    variant = 'outlined',
    label,
    size = 'medium',
    margin = 'normal',
    disabled,
    placeholder,
    description,
    required,
    fullWidth = true,
    search_endpoint,
    disableSuggestion = false,
    allowSpaceNewTag = false
  } = config;
  const { i18n } = useGlobal();

  const [field, meta, { setValue, setTouched }] = useField(name ?? 'tags');
  const [inputText, setInputText] = React.useState('');
  const valueField = field?.value || [];

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );
  const defaultValue: ItemShape[] = React.useMemo(() => {
    return field.value && isArray(field.value) ? field.value : [];
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleChange = (event, newValue) => {
    event?.preventDefault();

    if (event?.type === 'blur') return;

    setValue(formatInputData(newValue));
    setInputText('');
  };

  const [data, handleSuggestionQueryChange] = useSuggestions<ItemShape>({
    apiUrl: search_endpoint
  });

  const handleBlur = e => {
    field.onBlur(e);
    setTouched(true);

    // new tag when user is blur
    if (inputText && !(field.value || []).includes(inputText)) {
      setValue(formatInputData((field.value || []).concat([inputText])));
      setInputText('');
    }
  };

  const handleKeyDown = (evt?: any) => {
    if (inputText) {
      switch (evt?.keyCode) {
        case 9: {
          evt.preventDefault();

          if ((field.value || []).includes(inputText)) break;

          handleChange(evt, (field.value || []).concat([inputText]));
          setInputText('');
          break;
        }
        case 32: {
          if (allowSpaceNewTag) {
            evt.preventDefault();

            if ((field.value || []).includes(inputText)) break;

            handleChange(evt, (field.value || []).concat([inputText]));
            setInputText('');
            break;
          }
        }
      }
    }
  };

  const handleListingTags = (evt, data) => {
    handleChange(
      evt,
      valueField.concat(
        uniq(data.map(x => x.trim())).filter(x => x && !valueField.includes(x))
      )
    );
  };

  return (
    <FormControl
      variant={variant as any}
      margin={margin}
      fullWidth={fullWidth}
      size={size}
      data-testid={camelCase(`field ${name}`)}
    >
      <Autocomplete<string, true, boolean, boolean>
        clearText={i18n.formatMessage({ id: 'clear' })}
        closeText={i18n.formatMessage({ id: 'close' })}
        openText={i18n.formatMessage({ id: 'open' })}
        multiple
        freeSolo
        autoSelect
        value={field.value || []}
        selectOnFocus={false}
        filterSelectedOptions
        onBlur={handleBlur}
        id={`tags-${name}`}
        options={disableSuggestion ? [] : data.items}
        ChipProps={chipProps}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        onInputChange={(evt, values) => {
          const tagsInput = values.split(',');

          if (tagsInput.length > 1) {
            handleListingTags(evt, tagsInput);

            return;
          }

          setInputText(values || '');

          !disableSuggestion && handleSuggestionQueryChange(values, {});
        }}
        inputValue={inputText || ''}
        defaultValue={defaultValue}
        onChange={(event, newValue) => handleChange(event, newValue)}
        renderInput={params => (
          <Text
            {...params}
            required={required}
            label={label}
            placeholder={field?.value?.length ? '' : placeholder}
            inputProps={{
              ...params.inputProps,
              'data-testid': camelCase(`input ${name}`)
            }}
            error={haveError}
            onKeyDown={handleKeyDown}
          />
        )}
      />
      {haveError ? <ErrorMessage error={meta.error} /> : null}
      {description ? (
        <Typography sx={{ fontSize: '13px' }} color="text.hint" mt={1}>
          {description}
        </Typography>
      ) : null}
    </FormControl>
  );
}

export default TagsField;

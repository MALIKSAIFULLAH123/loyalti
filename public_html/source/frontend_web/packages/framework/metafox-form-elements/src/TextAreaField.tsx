/**
 * @type: formElement
 * name: form.element.Textarea
 * chunkName: formExtras
 */
import { useField } from 'formik';
import { camelCase, isString } from 'lodash';
import React, { createElement } from 'react';
import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import TextAreaControlEmoji from './TextArea/TextAreaControlEmoji';
import { Box, TextField, styled } from '@mui/material';

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& p, textarea::placeholder': {
    color: theme.palette.text.hint
  }
}));

const TextAreaField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps) => {
  const [field, meta, { setValue }] = useField(name ?? 'TextField');
  const inputRef = React.useRef<HTMLInputElement>();
  const {
    label,
    disabled,
    labelProps,
    placeholder,
    variant,
    margin = 'normal',
    fullWidth,
    type = 'text',
    rows = 5,
    description,
    autoFocus,
    required,
    maxLength,
    enableEmoji = false
  } = config;
  const { jsxBackend } = useGlobal();
  const Emoji = jsxBackend.get('textArea.control.attachEmoji');

  // fix: A component is changing an uncontrolled input
  if (!field.value) {
    field.value = config.defaultValue ?? '';
  }

  const haveError = Boolean(meta.error && (meta.touched || formik.submitCount));

  const handleEmojiClick = emojiIcon => {
    if (!inputRef?.current) return;

    const myField = inputRef.current;
    let newValue;
    const selection = myField.selectionEnd;

    if (selection || selection === 0) {
      newValue =
        myField.value.substring(0, selection) +
        emojiIcon +
        myField.value.substring(selection, myField.value.length);
    } else {
      newValue = (myField.value || '') + emojiIcon;
    }

    setValue(newValue);

    myField.value = newValue;
    myField.selectionEnd = selection + emojiIcon.length;
  };

  const handleBlur = e => {
    isString(field.value) && setValue(field.value.trim());
    field.onBlur(e);
  };

  return createElement(Text, {
    ...field,
    required: required || forceRequired,
    multiline: true,
    disabled: disabled || forceDisabled || formik.isSubmitting,
    variant,
    label,
    'data-testid': camelCase(`field ${name}`),
    autoFocus,
    inputProps: {
      'data-testid': camelCase(`input ${name}`),
      maxLength,
      ref: inputRef
    },
    rows,
    InputLabelProps: labelProps,
    placeholder,
    margin,
    error: haveError ? meta.error : false,
    fullWidth,
    type,
    helperText: haveError ? meta.error : description,
    onBlur: handleBlur,
    InputProps: {
      endAdornment:
        enableEmoji && Emoji ? (
          <Box sx={{ position: 'absolute', bottom: 0, right: '8px' }}>
            <Emoji control={TextAreaControlEmoji} onChange={handleEmojiClick} />
          </Box>
        ) : null
    }
  });
};

export default TextAreaField;

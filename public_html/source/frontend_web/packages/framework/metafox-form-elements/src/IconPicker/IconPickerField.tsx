/**
 * @type: formElement
 * name: form.element.IconPicker
 */
import { FormFieldProps } from '@metafox/form';
import { ClickOutsideListener } from '@metafox/ui';
import Description from '../Description';
import {
  FormControl,
  FormLabel,
  Paper,
  Popper,
  PopperProps,
  styled,
  TextField as MuiTextField
} from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import IconPicker from './IconPicker';

const TextFieldStyled = styled(MuiTextField, {
  shouldForwardProp: props => props !== 'flagDisabled'
})<{ flagDisabled?: boolean }>(({ theme, flagDisabled }) => ({
  ...(flagDisabled && {
    '& .MuiOutlinedInput-root': {
      '& > fieldset': {
        borderColor:
          theme.palette.mode === 'light'
            ? 'rgba(0, 0, 0, 0.23) !important'
            : 'rgba(255, 255, 255, 0.23) !important'
      }
    }
  })
}));

const IconPickerField = ({
  config,
  disabled: forceDisabled,
  name,
  formik
}: FormFieldProps) => {
  const [field, meta, { setValue }] = useField(name ?? 'TextField');

  const {
    label,
    hint,
    disabled,
    autoComplete,
    placeholder,
    noFeedback,
    variant,
    margin,
    fullWidth,
    type = 'text',
    rows,
    size,
    required,
    description,
    autoFocus,
    readOnly,
    maxLength,
    hasFormLabel = false
  } = config;

  // fix: A component is changing an uncontrolled input
  if (!field.value) {
    field.value = config.defaultValue ?? '';
  }

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  const handleBlur = () => {
    setValue(field?.value?.trim());
  };

  const [anchorEl, setAnchorEl] = React.useState<PopperProps['anchorEl']>(null);

  const popperRef = React.useRef();
  const [open, setOpen] = React.useState<boolean>(false);

  const handleFocus = () => {
    setOpen(true);
  };

  const onClickAway = React.useCallback(() => {
    setOpen(false);
  }, []);

  const handleIconClick = React.useCallback((value: string) => {
    setOpen(false);
    setValue(value);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <>
      <ClickOutsideListener excludeRef={popperRef} onClickAway={onClickAway}>
        <FormControl
          margin={margin}
          variant={variant}
          fullWidth={fullWidth}
          data-testid={camelCase(`field ${name}`)}
        >
          {hasFormLabel && (
            <FormLabel sx={{ my: 2 }} component="label">
              {label}
            </FormLabel>
          )}
          <TextFieldStyled
            {...field}
            flagDisabled={disabled || forceDisabled || formik.isSubmitting}
            disabled={disabled || forceDisabled || formik.isSubmitting}
            size={size}
            onBlur={handleBlur}
            onFocus={handleFocus}
            required={required}
            error={haveError}
            autoFocus={autoFocus}
            ref={setAnchorEl}
            inputProps={{
              'data-testid': camelCase(`input ${name}`),
              maxLength,
              autoComplete,
              readOnly
            }}
            label={!hasFormLabel ? label : undefined}
            rows={rows}
            placeholder={placeholder}
            type={type}
            helperText={
              noFeedback ? null : haveError && meta.error ? (
                meta.error
              ) : description ? (
                <Description text={description} hint={hint} />
              ) : null
            }
          />
        </FormControl>
      </ClickOutsideListener>
      <Popper
        ref={popperRef}
        open={open}
        anchorEl={anchorEl}
        placement="top-end"
        role="presentation"
        style={{ zIndex: 1300 }}
      >
        <Paper
          data-testid="dialogEmojiPicker"
          sx={{
            width: 500,
            height: 280,
            display: 'flex',
            flexDirection: 'column',
            bgcolor: 'background.paper',
            paddingTop: 1
          }}
        >
          <IconPicker onIconClick={handleIconClick} />
        </Paper>
      </Popper>
    </>
  );
};

export default IconPickerField;

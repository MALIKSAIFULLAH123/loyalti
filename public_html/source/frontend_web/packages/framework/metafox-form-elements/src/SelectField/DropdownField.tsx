/**
 * @type: formElement
 * name: form.element.Dropdown
 * chunkName: formBasic
 */
import {
  FormControl,
  FormHelperText,
  InputLabel,
  MenuItem,
  Select,
  SelectChangeEvent,
  IconButton,
  Typography
} from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import ArrowDropDownIcon from '@mui/icons-material/ArrowDropDown';
import Description from '../Description';
import { styled } from '@mui/material/styles';

const name = 'DropdownField';

const MenuItemStyled = styled(MenuItem, {
  name,
  slot: 'MenuItem',
  shouldForwardProp: props => props !== 'hasDescription'
})<{ hasDescription?: boolean }>(({ theme, hasDescription }) => ({
  ...(hasDescription && {
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'flex-start'
  })
}));

const DropdownField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps) => {
  const {
    label,
    margin,
    disabled,
    fullWidth,
    hiddenLabel,
    required,
    options: optionsProp = [],
    variant,
    size,
    style,
    sx,
    sxFieldWrapper,
    description,
    autoComplete = 'off',
    placement,
    className,
    relatedFieldName,
    optionRelatedMapping,
    sxOptions,
    disableScrollLock = false
  } = config;
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'DropdownField'
  );
  const [fieldRelated] = useField(relatedFieldName ?? `${name}_child`);
  const [optionRelatedState, setOptionRelatedState] = React.useState([]);
  const options = relatedFieldName ? optionRelatedState : optionsProp;

  const [open, setOpen] = React.useState(false);
  const actionMenuRef = React.useRef();

  const selectRef = React.useRef<HTMLDivElement | null>(null);

  React.useEffect(() => {
    if (!disableScrollLock) return;

    const checkIfAnchorOutsideViewport = () => {
      if (selectRef.current) {
        const rect = selectRef.current.getBoundingClientRect();
        const isOutside =
          rect.top < 0 ||
          rect.left < 0 ||
          rect.bottom >
            (window.innerHeight || document.documentElement.clientHeight) ||
          rect.right >
            (window.innerWidth || document.documentElement.clientWidth);

        if (isOutside) {
          setOpen(false);
        }
      }
    };

    const handleUpdatePosition = () => {
      if (actionMenuRef.current) {
        const popoverInstance = actionMenuRef.current;
        const updatePosition = (popoverInstance as any)?.updatePosition;

        if (typeof updatePosition === 'function') {
          updatePosition();
        }
      }
    };

    function onScroll(): void {
      handleUpdatePosition();
      checkIfAnchorOutsideViewport();
    }

    document.addEventListener('scroll', onScroll);

    return () => document.removeEventListener('scroll', onScroll);
  }, [disableScrollLock]);

  React.useEffect(() => {
    if (!relatedFieldName) return;

    if (optionRelatedMapping) {
      const optionRelated = optionRelatedMapping[fieldRelated?.value] || [];
      setOptionRelatedState(optionRelated);

      if (optionRelated.some(x => x.value === field.value)) {
        return;
      }

      const valueDefault = optionRelated.find(item => item?.is_default);

      setValue(valueDefault?.value);

      return;
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [relatedFieldName, fieldRelated?.value]);

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  if (!options.length) return null;

  const handleChange = (event: SelectChangeEvent) => {
    const { value } = event.target;
    setValue(value);
  };

  const handleBlur = e => {
    field.onBlur(e);
    setTouched(true);
  };

  const placementMenuProps =
    placement === 'right'
      ? {
          anchorOrigin: {
            vertical: 'bottom',
            horizontal: 'right'
          },
          transformOrigin: {
            vertical: 'top',
            horizontal: 'right'
          }
        }
      : {};

  return (
    <FormControl
      margin={margin ?? 'normal'}
      disabled={disabled}
      fullWidth={fullWidth}
      hiddenLabel={hiddenLabel}
      required={required || forceRequired}
      style={style}
      error={haveError}
      size={size}
      variant={variant as any}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
    >
      {label && <InputLabel>{label}</InputLabel>}
      <Select
        open={open}
        onOpen={() => setOpen(true)}
        onClose={() => setOpen(false)}
        onBlur={handleBlur}
        value={field.value ?? ''}
        displayEmpty
        label={label}
        defaultValue={options[0]?.value}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        onChange={handleChange}
        id={`select-${name}`}
        sx={sx}
        className={className}
        ref={selectRef}
        MenuProps={{
          ...placementMenuProps,
          disableScrollLock,
          action: actionMenuRef
        }}
        disable
        inputProps={{
          label,
          variant,
          size,
          error: haveError || undefined,
          autoComplete,
          required: required || forceRequired
        }}
        data-testid={camelCase(`input ${name}`)}
        renderValue={value => {
          const index = options?.findIndex(item => item.value === value);

          return index === -1 ? value : options?.[index]?.label;
        }}
        IconComponent={() => (
          <IconButton
            sx={{ pointerEvents: 'none', position: 'absolute', right: '7px' }}
          >
            <ArrowDropDownIcon />
          </IconButton>
        )}
      >
        {options.map((option, key) => (
          <MenuItemStyled
            value={option.value}
            key={key}
            sx={sxOptions}
            hasDescription={option.description}
          >
            {option.label}
            {option.description ? (
              <Typography variant="body2" color="text.secondary" mt={0.5}>
                {option.description}
              </Typography>
            ) : null}
          </MenuItemStyled>
        ))}
      </Select>
      {description && <Description text={description} />}
      {haveError && <FormHelperText>{meta?.error}</FormHelperText>}
    </FormControl>
  );
};

export default DropdownField;

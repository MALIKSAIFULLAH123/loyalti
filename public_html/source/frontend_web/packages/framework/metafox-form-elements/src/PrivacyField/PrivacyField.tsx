/**
 * @type: formElement
 * name: form.element.Privacy
 * chunkName: formElement
 */
import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  FormControl,
  FormHelperText,
  InputAdornment,
  InputLabel,
  MenuItem,
  Select,
  Typography,
  styled
} from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import ErrorMessage from '../ErrorMessage';
import useStyles from './PrivacyField.styles';

enum Privacy {
  Everyone = 0,
  Friends = 2,
  Friends_of_friends = 3,
  Only_me = 4,
  Custom = 10,
  Community = 1
}

const optionIcons = [
  {
    label: 'Public',
    value: Privacy.Everyone,
    icon: 'ico-globe'
  },
  {
    label: 'Community',
    value: Privacy.Community,
    icon: 'ico-user-circle'
  },
  {
    label: 'Friends',
    value: Privacy.Friends,
    icon: 'ico-user-two-men'
  },
  {
    label: 'Friends of Friends',
    value: Privacy.Friends_of_friends,
    icon: 'ico-user-man-three'
  },
  {
    label: 'Only Me',
    value: Privacy.Only_me,
    icon: 'ico-lock'
  },
  {
    label: 'Custom',
    value: Privacy.Custom,
    icon: 'ico-gear'
  }
];

const privacyIcon = (value: Privacy): string => {
  // eslint-disable-next-line eqeqeq
  return optionIcons.find(item => item.value == value)?.icon;
};

const StartIcon = styled(InputAdornment, { name: 'AvatarWrapper' })(
  ({ theme }) => ({
    position: 'absolute',
    left: theme.spacing(2),
    width: theme.spacing(3),
    justifyContent: 'center'
  })
);

export default function PrivacyField({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const [field, meta, { setValue }] = useField(name ?? 'PrivacyField');
  const {
    disabled,
    description,
    options,
    label,
    variant,
    fullWidth,
    defaultValue,
    minWidth,
    sx
  } = config;
  const { dialogBackend } = useGlobal();
  const classes = useStyles();

  const [privacyValue, setPrivacyValue] = React.useState(
    field.value || defaultValue || Privacy.Everyone
  );

  const handleClick = React.useCallback(
    value => {
      if (value === Privacy.Custom) {
        selectCustomPrivacy();
      } else {
        setPrivacyValue(value);
        setValue(value);
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [privacyValue]
  );

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  const selectCustomPrivacy = () => {
    dialogBackend
      .present({
        component: 'dialog.DialogCustomListPrivacy',
        props: {
          value: privacyValue
        }
      })
      .then(value => {
        if (value) {
          setValue(value);
          setPrivacyValue(value);
        }
      });
  };

  const currentOption = options.find((x: Record<string, any>) =>
    Array.isArray(privacyValue)
      ? x.value === Privacy.Custom
      : x.value === privacyValue
  );

  return (
    <FormControl
      margin="normal"
      fullWidth={fullWidth}
      variant={variant as any}
      sx={{ ...sx, minWidth }}
      data-testid={camelCase(`field ${name}`)}
    >
      <Select
        className={classes.root}
        value={Array.isArray(privacyValue) ? Privacy.Custom : privacyValue}
        sx={{ paddingLeft: 0 }}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        onBlur={field.onBlur}
        label={label}
        startAdornment={
          <StartIcon position="start">
            <LineIcon
              icon={privacyIcon(
                Array.isArray(privacyValue) ? Privacy.Custom : privacyValue
              )}
            />
          </StartIcon>
        }
        MenuProps={{
          anchorOrigin: {
            vertical: 'bottom',
            horizontal: 'left'
          },
          getContentAnchorEl: null,
          transformOrigin: {
            vertical: 'top',
            horizontal: 'left'
          },
          'data-testid': camelCase(`menu ${name}`),
          PaperProps: {
            style: {
              maxHeight: 200
            }
          }
        }}
      >
        {options
          ? options.map((item, index) => (
              <MenuItem
                value={item.value}
                data-value={item.value}
                key={index.toString()}
                onClick={() => handleClick(item.value)}
              >
                {item.label}
              </MenuItem>
            ))
          : null}
      </Select>
      <InputLabel id={`label-${name}`}>{label}</InputLabel>
      {currentOption?.description ? (
        <FormHelperText>{currentOption?.description}</FormHelperText>
      ) : null}
      {description ? (
        <Typography sx={{ fontSize: '13px' }} color="text.hint" mt={1}>
          {description}
        </Typography>
      ) : null}
      {haveError && <ErrorMessage error={meta.error} />}
    </FormControl>
  );
}

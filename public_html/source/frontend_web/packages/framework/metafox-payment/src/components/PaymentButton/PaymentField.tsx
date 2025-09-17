/**
 * @type: formElement
 * name: form.element.GatewayButton
 * chunkName: formExtras
 */

import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { Button as MuiButton, FormControl, styled, Box } from '@mui/material';
import { useField, useFormikContext } from 'formik';
import React from 'react';
import { camelCase, isEmpty, get } from 'lodash';
import { LineIcon } from '@metafox/ui';
import { Description } from '@metafox/form-elements';

const Item = styled(Box, { name: 'Item' })(({ theme }) => ({
  display: 'block',
  '& iframe': {
    maxWidth: 'none'
  }
}));

const prefixElement = 'payment.element.GatewayButton.';

export default function PaymentGatewayField(props: FormFieldProps) {
  const { dialogBackend, setNavigationConfirm, jsxBackend } = useGlobal();
  const { config, name, disabled: forceDisabled, formik } = props;
  const {
    gateway_field_name,
    gateway_id,
    variant,
    fullWidth = true,
    margin = 'normal',
    disabled,
    label,
    size,
    confirmation,
    description,
    icon,
    button_props = {}
  } = config;
  const [field, , { setValue }] = useField(
    gateway_field_name ?? 'PaymentGatewayField'
  );
  const refClicked = React.useRef(false);
  const GatewayButton = jsxBackend.get(`${prefixElement}${variant}`);

  const { errors, initialValues } = useFormikContext();
  const [show, setShow] = React.useState(true);

  React.useEffect(() => {
    if (!refClicked.current) return;

    if (field?.value === gateway_id) {
      if (confirmation) {
        dialogBackend.confirm(confirmation).then(ok => {
          if (!ok) {
            setValue(get(initialValues, name));

            return;
          }

          formik.submitForm().finally(() => {
            setValue(undefined);
            setNavigationConfirm(false);
          });
        });

        return;
      }

      formik.submitForm().finally(() => {
        setValue(undefined);
        setNavigationConfirm(false);
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [field?.value, refClicked.current]);

  const handleClick = () => {
    if (!isEmpty(errors)) return;

    refClicked.current = true;

    setValue(gateway_id);
  };

  if (!show) return null;

  return (
    <FormControl
      fullWidth={fullWidth}
      margin={margin}
      size={size}
      data-testid={camelCase(`field ${name}`)}
    >
      <Item>
        {GatewayButton ? (
          <GatewayButton
            {...props}
            setShow={setShow}
            handleClickDefault={handleClick}
          />
        ) : (
          <MuiButton
            variant="outlined"
            fullWidth={fullWidth}
            disabled={disabled || forceDisabled || formik.isSubmitting}
            data-testid={camelCase(`button ${name}`)}
            onClick={handleClick}
            startIcon={<LineIcon icon={icon} />}
            {...(button_props || {})}
          >
            {label}
          </MuiButton>
        )}
      </Item>
      {description ? <Description text={description} /> : null}
    </FormControl>
  );
}

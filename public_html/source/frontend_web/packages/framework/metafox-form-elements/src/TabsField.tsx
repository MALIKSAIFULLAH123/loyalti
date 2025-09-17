/**
 * @type: formElement
 * name: form.element.Tabs
 * chunkName: formBasic
 */

import { useField } from 'formik';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { FormControl, styled, Tab, Tabs } from '@mui/material';
import { camelCase, isEmpty } from 'lodash';
import { useGlobal } from '@metafox/framework';
import { filterShowWhen } from '@metafox/utils';

const name = 'TabsField';

const TabItemStyled = styled(Tab, {
  name,
  slot: 'TabItemStyled'
})(({ theme }) => ({
  fontWeight: 'bold',
  fontSize: theme.mixins.pxToRem(15),
  width: 'fit-content !important',
  minWidth: 'fit-content !important',
  padding: '0 !important',
  marginLeft: theme.spacing(2),
  '&:first-of-type': {
    marginLeft: theme.spacing(0)
  }
}));

const TabsField = ({ name, config }: FormFieldProps) => {
  const {
    variant,
    margin,
    fullWidth,
    sxFieldWrapper,
    options: optionConfig = []
  } = config;

  const { i18n, getAcl, getSetting, useSession } = useGlobal();
  const [field, , { setValue }] = useField(name ?? 'TabsField');

  const acl = getAcl();
  const session = useSession();
  const setting = getSetting();

  const changeTab = (_: any, value: any) => {
    setValue(value);
  };

  const options = filterShowWhen(optionConfig, {
    setting,
    session,
    acl
  });

  if (isEmpty(options)) return null;

  return (
    <FormControl
      margin={margin}
      variant={variant}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      id={name}
      sx={sxFieldWrapper}
    >
      <Tabs
        value={field?.value ?? options?.[0]?.value}
        onChange={changeTab}
        indicatorColor="primary"
        textColor="primary"
      >
        {options.map(itemTab => (
          <TabItemStyled
            key={itemTab?.value}
            value={itemTab?.value}
            label={i18n.formatMessage({ id: itemTab?.label })}
          ></TabItemStyled>
        ))}
      </Tabs>
    </FormControl>
  );
};

export default TabsField;

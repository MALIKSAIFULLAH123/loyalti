/**
 * @type: formElement
 * name: form.element.IconButton
 * chunkName: formBasic
 */

import { useGlobal } from '@metafox/framework';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { Tooltip, IconButton } from '@mui/material';
import { LineIcon } from '@metafox/ui';
import { camelCase } from 'lodash';

function IconButtonField({ config, name, formik }: FormFieldProps) {
  const { icon, tooltip, linkTo } = config;
  const { i18n, navigate } = useGlobal();

  const handleClick = (evt: React.FormEvent<HTMLFormElement>) => {
    if (evt) {
      evt.preventDefault();
    }

    if (linkTo) {
      navigate({
        pathname: linkTo
      });
    }
  };

  return (
    <Tooltip title={i18n.formatMessage({ id: tooltip })}>
      <IconButton
        data-testid={camelCase(`button ${name}`)}
        onClick={handleClick}
        size="small"
      >
        <LineIcon icon={icon} />
      </IconButton>
    </Tooltip>
  );
}
export default IconButtonField;

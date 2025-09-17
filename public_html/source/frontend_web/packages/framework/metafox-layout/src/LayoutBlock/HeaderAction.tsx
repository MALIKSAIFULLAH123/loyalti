import { RouteLink, useGlobal } from '@metafox/framework';
import { Button } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const MuiHeaderAction = styled(Button, {
  name: 'GeneralBlock',
  slot: 'headerAction'
})({
  whiteSpace: 'nowrap'
});

export default function HeaderAction({
  label,
  size = 'small',
  to,
  variant = 'link',
  color = 'primary',
  showWhen,
  ...rest
}) {
  const { i18n, compactUrl, usePageParams } = useGlobal();
  const params = usePageParams();

  return (
    <MuiHeaderAction
      component={RouteLink}
      to={compactUrl(to, params)}
      color={color}
      size={size}
      disableFocusRipple
      disableRipple
      variant={variant}
      {...rest}
    >
      {i18n.formatMessage({ id: label })}
    </MuiHeaderAction>
  );
}

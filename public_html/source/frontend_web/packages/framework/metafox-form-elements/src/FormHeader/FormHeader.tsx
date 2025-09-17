/**
 * @type: formElement
 * name: form.element.FormHeader
 * chunkName: formBasic
 */
import { DialogTitle } from '@metafox/dialog';
import { FormFieldProps, useFormSchema } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, IconButton, styled, Typography } from '@mui/material';
import React from 'react';

const HeaderRoot = styled(Box, {
  name: 'Form',
  slot: 'Header'
})({
  display: 'block',
  flexShrink: 1
});

type Props = FormFieldProps & { noHeader: boolean };

const BackButton = ({ icon = 'ico-arrow-left', ...restProps }) => {
  const { navigate } = useGlobal();

  const handleClick = () => {
    navigate(-1);
  };

  return (
    <IconButton
      size="small"
      role="button"
      id="back"
      data-testid="buttonBack"
      sx={{ transform: 'translate(-7px,0)' }}
      onClick={handleClick}
      {...restProps}
    >
      <LineIcon icon={icon} />
    </IconButton>
  );
};

export default function FormHeader({ config }: Props) {
  const { sx } = config;
  const schema = useFormSchema();
  const { title, backProps, noHeader, noTitle, description, dialog, onCancel } =
    schema;

  if (dialog) {
    return <DialogTitle onCancel={onCancel}>{title}</DialogTitle>;
  }

  if (noHeader) {
    return null;
  }

  return (
    <HeaderRoot sx={sx}>
      {title && !noTitle ? (
        <Typography component="h1" variant="h3" sx={{ display: 'flex' }}>
          {!dialog && backProps ? <BackButton {...backProps} /> : null}
          {title}
        </Typography>
      ) : null}
      {description ? (
        <Typography
          component="p"
          variant="body2"
          sx={{ whiteSpace: 'pre-line' }}
        >
          {description}
        </Typography>
      ) : null}
    </HeaderRoot>
  );
}

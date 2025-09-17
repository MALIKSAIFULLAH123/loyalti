import { FormBuilder } from '@metafox/form';
import { useResourceForm } from '@metafox/framework';
import { Box } from '@mui/material';
import React from 'react';

type Props = {
  action: string;
};

const FormResend = ({ action = 'resend' }: Props) => {
  const formSchema = useResourceForm('user', 'user_verify', action);

  if (!formSchema) return null;

  return (
    <Box mt={2}>
      <FormBuilder navigationConfirmWhenDirty={false} formSchema={formSchema} />
    </Box>
  );
};
export default FormResend;

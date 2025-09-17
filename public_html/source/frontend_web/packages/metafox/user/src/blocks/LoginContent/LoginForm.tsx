import { AuthUserShape, useResourceForm } from '@metafox/framework';
import { FormBuilder } from '@metafox/form';
import { Container } from '@mui/material';
import React from 'react';

type Props = {
  user?: any;
  submitAction?: string;
  formName?: string;
  title?: string;
};

export default function LoginForm({
  user: previousUser,
  submitAction = '@login',
  formName = 'login',
  title
}: Props) {
  const [user, setUser] = React.useState<AuthUserShape>();

  const formSchema = useResourceForm('user', 'user', formName);

  React.useEffect(() => {
    if (previousUser) {
      setUser(previousUser);
    }
  }, [previousUser]);

  const initialValues = {
    email: user?.email || user?.phone_number || user?.user_name,
    password: user?.password
  };

  return (
    <Container component="main" maxWidth="xs">
      <FormBuilder
        key={user?.id || 'none'}
        submitAction={submitAction}
        initialValues={initialValues}
        formSchema={title ? { ...formSchema, title } : formSchema}
        navigationConfirmWhenDirty={false}
      />
    </Container>
  );
}

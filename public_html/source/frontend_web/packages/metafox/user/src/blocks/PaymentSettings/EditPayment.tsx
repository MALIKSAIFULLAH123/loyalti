// hooks
import { SmartFormBuilder } from '@metafox/form';
import { useGlobal, useSession } from '@metafox/framework';
// actions
import { RELOAD_ACCOUNT } from '@metafox/user/actions/accountSettings';
import { Button, styled, Box, Typography } from '@mui/material';
// styles
import React, { useState } from 'react';

const ButtonAction = styled(Box, {
  name: 'ButtonAction'
})(({ theme }) => ({
  display: 'inline-flex'
}));

type Props = {
  data: Record<string, any>;
};

export default function EditableEmail({ data }: Props) {
  const { i18n } = useGlobal();
  const { user } = useSession();

  const [isEdit, setEdit] = useState(false);
  const { form_api_url, title, is_active } = data;

  if (!is_active) return null;

  return (
    <Box data-testid={`edit${title}`}>
      <Box
        sx={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          width: '100%'
        }}
      >
        <Box sx={{ flex: 1, minWidth: 0 }}>
          <Typography variant="body1" fontWeight={600}>
            {title}
          </Typography>
          {isEdit ? (
            <Box pt={1}>
              <SmartFormBuilder
                successAction={RELOAD_ACCOUNT}
                dataSource={{ apiUrl: form_api_url }}
                pageParams={{ id: user?.id }}
                onSuccess={() => setEdit(false)}
                onCancel={() => setEdit(false)}
              />
            </Box>
          ) : null}
        </Box>
        <ButtonAction>
          {!isEdit && (
            <Button
              data-testid="buttonEdit"
              size={'medium'}
              variant={'outlined'}
              color={'primary'}
              onClick={() => setEdit(true)}
            >
              {i18n.formatMessage({ id: 'edit' })}
            </Button>
          )}
        </ButtonAction>
      </Box>
    </Box>
  );
}

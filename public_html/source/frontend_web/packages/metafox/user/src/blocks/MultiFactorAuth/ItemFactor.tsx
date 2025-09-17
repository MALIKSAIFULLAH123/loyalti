import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  FETCH_MFA_FORM_SERVICE,
  REMOVE_MFA_SERVICE
} from '@metafox/user/actions/accountSettings';
import { Button, styled, Box, Typography } from '@mui/material';
import React from 'react';

const ButtonAction = styled(Box, {
  name: 'ButtonAction'
})(({ theme }) => ({
  display: 'inline-flex',
  marginLeft: theme.spacing(1)
}));

const LineIconStyled = styled(LineIcon, {
  name: 'LineIconStyled'
})(({ theme }) => ({
  fontSize: 44,
  color: theme.palette.grey['600'],
  padding: theme.spacing(4),
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center'
}));

const WrapperContentStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  width: '100%'
}));

type Props = {
  data: Record<string, any>;
};

export default function EditableEmail({ data }: Props) {
  const { i18n, dispatch } = useGlobal();

  if (!data) return;

  const { title, is_active, icon, description, service, is_enable } = data;

  const handleAction = () => {
    if (is_active) {
      dispatch({
        type: REMOVE_MFA_SERVICE,
        payload: {
          service
        }
      });

      return;
    }

    dispatch({
      type: FETCH_MFA_FORM_SERVICE,
      payload: {
        service
      }
    });
  };

  return (
    <Box data-testid={`edit${title}`} className="item">
      <Typography variant="body1" fontWeight={600}>
        {title}
      </Typography>
      <WrapperContentStyled>
        <Box sx={{ display: 'flex', alignItems: 'center', minWidth: 0 }}>
          <LineIconStyled icon={icon} />
          <Typography variant="body1">{description}</Typography>
        </Box>
        <ButtonAction>
          <Button
            data-testid="buttonEdit"
            size={'medium'}
            variant={is_active ? 'outlined' : 'contained'}
            color={'primary'}
            onClick={handleAction}
            disabled={!is_enable}
          >
            {i18n.formatMessage({ id: is_active ? 'remove' : 'enable' })}
          </Button>
        </ButtonAction>
      </WrapperContentStyled>
    </Box>
  );
}

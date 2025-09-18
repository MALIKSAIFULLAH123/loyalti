import { useGlobal } from '@metafox/framework';
import { Button, styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';

const ButtonAction = styled(Button, {
  name: 'ListButton',
  shouldForwardProp: props => props !== 'nameButton'
})<{
  nameButton?: string;
}>(({ theme, nameButton }) => ({
  marginRight: theme.spacing(1),
  marginTop: theme.spacing(1),
  [theme.breakpoints.down('sm')]: {
    ...(nameButton === 'payment' && {
      width: '100%'
    })
  }
}));

function ListButtonAction({ identity, menu, item: itemAdvertise }: any) {
  const { i18n, dispatch } = useGlobal();

  if (isEmpty(menu)) return null;

  const handleAction = (type: string) => {
    dispatch({
      type,
      payload: {
        identity
      }
    });
  };

  return (
    <>
      {menu.map(item => (
        <ButtonAction
          key={item.name}
          onClick={() => handleAction(item.value)}
          data-testid={`button${item.name}`}
          color={item?.color || 'primary'}
          variant={item?.variant || 'outlined'}
          nameButton={item.name}
        >
          {i18n.formatMessage(
            { id: item?.label },
            { price: itemAdvertise?.payment_price }
          )}
        </ButtonAction>
      ))}
    </>
  );
}

export default ListButtonAction;

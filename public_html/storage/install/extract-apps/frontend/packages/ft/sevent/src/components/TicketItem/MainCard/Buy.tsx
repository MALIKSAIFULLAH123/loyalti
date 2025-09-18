import React, { useState } from 'react';
import { Button, TextField, IconButton } from '@mui/material';
import { Add, Remove } from '@mui/icons-material';
import { useTheme } from '@mui/system';
import { useGlobal } from '@metafox/framework';

const Buy = ({ identity, i18n, item, actions }) => {
  const theme = useTheme();
  const [qty, setQty] = useState(1); 
  const [load, setLoad] = useState(false);
  const { dispatch, apiClient, toastBackend, navigate } = useGlobal();

  const handleInputChange = (event) => {
    let newQty = parseInt(event.target.value, 10);

    if (isNaN(newQty) || newQty < 1) {
      newQty = 1;
    } else if (newQty > item.remaining_qty && item.is_unlimited != 1) {
      newQty = item.remaining_qty;
    }

    setQty(newQty);
  };

  const incrementQty = () => {
    if (qty < item.remaining_qty || item.is_unlimited == 1) {
      setQty(qty + 1);
    }
  };

  const decrementQty = () => {
    if (qty > 1) {
      setQty(qty - 1);
    }
  };

  const handlePayment = async () => {
    setLoad(true);

    if (item.amount > 0) {
      apiClient
        .request({
          url: `sevent/setupQty?ticket_id=${item.id}&qty=${qty}`,
          method: 'GET'
        })
        .then((res) => {
          dispatch({
            type: 'sevent/paymentItem',
            payload: {
              identity
            }
          });
          setLoad(false);
        })
        .catch((err) => {
          setLoad(false); 
        });
    } else {
      dispatch({ type: 'sevent/free', payload: { identity, qty } });

      setTimeout(() => {
        toastBackend.success(i18n.formatMessage({ id: 'sevent_purchased_successfully' }));
        navigate('/sevent/ticket/my');
      }, 1000);
      
    }
  };

  const handleButtonClick = () => {
    handlePayment();
  };

  return (
    <div
      style={{
        marginTop: '8px',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center'
      }}
    >
      <div
        style={{
          display: 'flex',
          alignItems: 'center', // Aligned center for vertical centering
          width: '100%'
        }}
      >
        <IconButton onClick={decrementQty} disabled={qty <= 1 || load}>
          <Remove />
        </IconButton>

        <TextField
          type="number"
          variant="outlined"
          label={i18n.formatMessage({ id: 'sevent_qty' })}
          size="small"
          value={qty}
          onChange={handleInputChange}
          style={{ maxWidth: '100px', textAlign: 'center' }}
        />

        <IconButton onClick={incrementQty} disabled={(qty >= item.remaining_qty && item.is_unlimited != 1) || load}>
          <Add />
        </IconButton>

        <Button
          variant="contained"
          color="primary"
          onClick={handleButtonClick}
          disabled={load}
          style={{ flex: 1, maxWidth: '200px' }}
        >
          {i18n.formatMessage({ id: 'sevent_buy_button' })}
        </Button>
      </div>
      {item.amount > 0 ? (
        <div
          style={{
            fontSize: '12px',
            marginTop: '8px',
            color: theme.palette.text.secondary
          }}
        >
          {i18n.formatMessage({ id: 'buy_event_desc' })}
        </div>
      ) : (<br/>)}
    </div>
  );
};

export default Buy;

import React, { useState, useMemo } from 'react';
import { Button } from '@mui/material';
import { useGlobal } from '@metafox/framework';

export default function Favourite({ item }) {
  const [favourite, setFavourite] = useState(item.is_favourite);
  const { i18n, dispatch, useTheme, useLoggedIn } = useGlobal();
  const identity = item?._identity;
  const isLoggedIn = useLoggedIn();
  const theme = useTheme();

  const updateFavourite = () => {
    setFavourite(prevFavourite => !prevFavourite);
    dispatch({ type: 'sevent/favourite', payload: { identity } });
  };

  const buttonText = useMemo(() => {
    return favourite
      ? i18n.formatMessage({ id: 'remove_sevent_favourite' })
      : i18n.formatMessage({ id: 'add_sevent_favourite' });
  }, [favourite, i18n]);

  return isLoggedIn ? (
    <Button
      variant="outlined"
      size='small'
      style={theme.palette.mode !== 'light' ? { color: '#eee' } : null}
      onClick={updateFavourite}
      color="secondary"
      startIcon={<i className={!favourite ? 'ico ico-star-o' : 'ico ico-star'} />}
    >
      {buttonText}
    </Button>
  ) : null;
}

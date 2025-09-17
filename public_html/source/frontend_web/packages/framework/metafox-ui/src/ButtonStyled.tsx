import { Button, styled, useTheme } from '@mui/material';
import React from 'react';

const BntStyled = styled(Button, { name: 'ButtonStyled' })(({ theme }) => ({
  ...(theme.palette.mode === 'dark' && {
    backgroundColor: theme.palette.grey['700'],
    color: theme.palette.grey['400'],
    '&:hover': {
      backgroundColor: theme.palette.grey['600']
    }
  })
}));

function ButtonStyled({ variant, children, ...props }) {
  const theme = useTheme();

  return (
    <BntStyled
      variant={theme.palette.mode === 'dark' ? 'contained' : variant}
      {...props}
    >
      {children}
    </BntStyled>
  );
}

export default ButtonStyled;

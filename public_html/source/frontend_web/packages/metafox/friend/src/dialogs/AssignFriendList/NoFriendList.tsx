import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, styled } from '@mui/material';
import * as React from 'react';

const Root = styled('div', { name: 'root' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'center',
  padding: theme.spacing(0, 2),
  alignItems: 'center',
  marginTop: theme.spacing(11.25)
}));

const LineIconStyled = styled(LineIcon, { name: 'LineIcon' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(64),
  color: theme.palette.text.secondary,
  marginBottom: theme.spacing(4)
}));

const ContentStyled = styled('div', { name: 'content' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(18),
  color: theme.palette.text.secondary,
  textAlign: 'center',
  [theme.breakpoints.down('xs')]: {
    fontSize: theme.mixins.pxToRem(15)
  }
}));

const WrapButton = styled(Button, { name: 'WrapButton' })(({ theme }) => ({
  marginTop: theme.spacing(1),
  '&:hover': {
    backgroundColor: 'unset'
  }
}));

const ButtonInviteStyled = styled(Button, { name: 'ButtonInviteStyled' })(
  ({ theme }) => ({
    height: '100%',
    '& .MuiButton-startIcon': {
      marginLeft: 0
    }
  })
);

interface NoContentWithIconProps {
  icon: string;
  title?: string;
  description?: string;
  buttonCustom?: any;
}

export default function NoFriendList({
  icon = 'ico-list-bullet-o',
  description = 'there_are_no_friend_list',
  buttonCustom
}: NoContentWithIconProps) {
  const { i18n, dispatch, useDialog } = useGlobal();
  const { closeDialog } = useDialog();

  const handleClick = () => {
    closeDialog();

    if (buttonCustom.type) {
      dispatch({
        type: buttonCustom.type
      });
    }
  };

  return (
    <Root>
      <LineIconStyled icon={icon} />
      {description ? (
        <ContentStyled>{i18n.formatMessage({ id: description })}</ContentStyled>
      ) : null}
      {buttonCustom ? (
        <WrapButton>
          <ButtonInviteStyled
            size="small"
            variant="outlined"
            component="h5"
            onClick={handleClick}
            startIcon={
              <LineIcon
                sx={{ marginLeft: '0 !important' }}
                icon={'ico-envelope'}
              />
            }
          >
            <span>
              {i18n.formatMessage({
                id: buttonCustom.label || 'add_new_listing'
              })}
            </span>
          </ButtonInviteStyled>
        </WrapButton>
      ) : null}
    </Root>
  );
}

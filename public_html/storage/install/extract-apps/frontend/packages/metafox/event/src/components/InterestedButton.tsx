import { HandleAction, useGlobal } from '@metafox/framework';
import { ButtonAction, LineIcon } from '@metafox/ui';
import { IconButton, styled, Typography } from '@mui/material';
import React from 'react';
import { mappingRSVP } from '../utils';
import { NOT_INTERESTED } from '../constants';
import { camelCase } from 'lodash';

const name = 'InterestedButton';

const IconButtonAction = styled(IconButton, {
  name,
  slot: 'IconButtonAction',
  overridesResolver(props, styles) {
    return [styles.containedSizeMedium];
  }
})(({ theme, disabled }) => ({
  width: '100%',
  height: '100%',
  ...(!disabled &&
    theme.palette.mode === 'dark' && {
      color: theme.palette.primary.main,
      '&:hover': {
        backgroundColor: theme.palette.grey[700]
      }
    })
}));

const ButtonNoInterestedAction = styled(ButtonAction, {
  name,
  slot: 'ButtonNoInterestedAction',
  overridesResolver(props, styles) {
    return [styles.containedSizeMedium];
  }
})(({ theme, disabled }) => ({
  width: 'auto',
  flex: 1,
  height: '100%',

  ...(!disabled &&
    theme.palette.mode === 'dark' && {
      '&:hover': {
        backgroundColor: theme.palette.grey[700]
      }
    })
}));

const TypographyStyled = styled(Typography)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  fontWeight: '600'
}));

const OwnerStyled = styled(Typography)(({ theme }) => ({
  backgroundColor: theme.palette.background.default,
  borderRadius: theme.shape.borderRadius / 2,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  height: '100%',
  padding: `0 ${theme.spacing(2)}`,
  minHeight: '40px',
  flex: 1
}));

interface InterestedButtonProp {
  handleAction: HandleAction;
  identity: string;
  rsvp: number;
  disabled: boolean;
  menus?: Array<Record<string, any>>;
  fullWidth?: boolean;
  sizeButton?: string;
}

export default function InterestedButton({
  handleAction,
  identity,
  rsvp,
  disabled,
  menus,
  fullWidth = false,
  sizeButton = 'medium'
}: InterestedButtonProp) {
  const { i18n, ItemActionMenu, useGetItem, useSession, dispatch } =
    useGlobal();
  const item = useGetItem(identity);
  const user = useGetItem(item?.user);
  const { user: authUser } = useSession();
  const isOwner = authUser?.id === user?.id;
  const { icon, label } = mappingRSVP(rsvp);

  const handleClickInterested = onSuccess => {
    dispatch({
      type: 'interestedEvent',
      payload: { identity },
      meta: { onSuccess }
    });
  };

  if (isOwner) {
    return (
      <OwnerStyled>
        <Typography variant={'body1'} fontWeight={600} color="text.hint">
          {i18n.formatMessage({ id: 'your_event' })}
        </Typography>
      </OwnerStyled>
    );
  }

  if (rsvp === NOT_INTERESTED) {
    return (
      <ButtonNoInterestedAction
        disabled={disabled}
        color="secondary"
        variant="outlined-square"
        size={sizeButton}
        isIcon
        action={handleClickInterested}
        data-testid={camelCase('button interested')}
      >
        <LineIcon icon={icon} sx={{ mr: 1, fontSize: 13 }} />
        <TypographyStyled variant="body1">
          {i18n.formatMessage({ id: 'interested' })}
        </TypographyStyled>
      </ButtonNoInterestedAction>
    );
  }

  return (
    <ItemActionMenu
      sx={{ width: fullWidth ? '100%' : 'auto', height: '100%' }}
      label={i18n.formatMessage({ id: label })}
      id="interested"
      items={menus}
      handleAction={handleAction}
      menuName={'interestedMenu'}
      identity={identity}
      control={
        <IconButtonAction
          disabled={disabled}
          color="primary"
          variant="outlined-square"
          size={sizeButton}
        >
          <LineIcon icon={icon} sx={{ mr: 1, fontSize: 13 }} />
          <TypographyStyled variant="body1">
            {i18n.formatMessage({ id: label })}
          </TypographyStyled>
          <LineIcon icon={'ico-caret-down'} sx={{ ml: 1, fontSize: 18 }} />
        </IconButtonAction>
      }
    />
  );
}

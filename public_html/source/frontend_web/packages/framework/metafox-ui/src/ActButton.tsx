import { LineIcon } from '@metafox/ui';
import { ButtonProps, IconButton } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const name = 'ActBtn';

const ActBtnRoot = styled(IconButton, {
  name,
  slot: 'Root',
  shouldForwardProp: prop => prop !== 'color' && prop !== 'minimize'
})<{ color: string; minimize?: boolean }>(({ theme, color, minimize }) => ({
  flex: 'inherit',
  width: '100%',
  minWidth: theme.spacing(4),
  height: theme.spacing(4),
  padding: minimize ? 0 : theme.spacing(1, 3.5),
  display: 'inline-flex',
  justifyContent: 'center',
  alignItems: 'center',
  borderRadius: '999px !important',
  textTransform: 'capitalize',
  fontSize: theme.mixins.pxToRem(13),
  color: color || theme.palette.text.secondary,
  WebkitTouchCallout: 'none',
  WebkitUserSelect: 'none',
  userSelect: 'none',
  ':hover': {
    background: theme.palette.background.default
  },
  [theme.breakpoints.down('sm')]: {
    padding: minimize ? 0 : theme.spacing(1),
    width: 'auto'
  }
}));

const ActBtnMini = styled('span', {
  name,
  slot: 'SmallButton',
  shouldForwardProp: prop => prop !== 'color'
})<{ color: string }>(({ theme, color }) => ({
  fontSize: theme.mixins.pxToRem(13),
  color: color || theme.palette.text.secondary,
  '&::before': {
    display: 'inline-block'
  },
  '&:hover': {
    textDecoration: 'underline'
  }
}));

const ActBtnLabel = styled('span', {
  name,
  slot: 'Label'
})(({ theme }) => ({
  marginLeft: theme.spacing(1),
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  display: 'inline-block',
  [theme.breakpoints.down('sm')]: {
    fontWeight: 'normal'
  }
}));

const ActBtnImg = styled('img', {
  name,
  slot: 'Img'
})(({ theme }) => ({
  display: 'inline-flex',
  width: 24,
  minWidth: 24,
  height: 24,
  WebkitTouchCallout: 'none',
  WebkitUserSelect: 'none',
  userSelect: 'none'
}));

export type ActButtonProps = ButtonProps & {
  minimize?: boolean;
  icon?: string;
  src?: string;
  label?: string;
  iconVariant?: string;
  color?: string;
  variant?: 'button';
  'data-testid': string;
  popover?: string;
};

const ActButton = (
  { minimize, icon, src, color, label, ...rest }: ActButtonProps,
  ref: any
) => {
  if (minimize) {
    return (
      <ActBtnMini role={'button'} color={color} {...rest}>
        {label}
      </ActBtnMini>
    );
  }

  if (!label) {
    return (
      <ActBtnRoot {...rest} color={color} minimize ref={ref}>
        {src ? <ActBtnImg draggable={false} src={src} alt={label} /> : null}
        {!src && icon ? (
          <LineIcon
            icon={icon}
            sx={{
              color: 'text.secondary',
              fontSize: '18px',
              display: 'inline-flex'
            }}
          />
        ) : null}
      </ActBtnRoot>
    );
  }

  return (
    <ActBtnRoot {...rest} ref={ref} color={color} size="small">
      {src ? <ActBtnImg draggable={false} src={src} alt={label} /> : null}
      {!src && icon ? (
        <LineIcon
          icon={icon}
          sx={{
            color: 'text.secondary',
            fontSize: '18px',
            display: 'inline-flex'
          }}
        />
      ) : null}
      {label ? <ActBtnLabel children={label} /> : null}
    </ActBtnRoot>
  );
};

export default React.forwardRef(ActButton);

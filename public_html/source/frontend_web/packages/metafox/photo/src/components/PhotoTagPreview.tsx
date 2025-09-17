import { PhotoTagShape } from '@metafox/photo/types';
import { LineIcon } from '@metafox/ui';
import { UserItemShape } from '@metafox/user/types';
import { Button, styled } from '@mui/material';
import React from 'react';

const name = 'PhotoTag';
const Root = styled('div', {
  name,
  slot: 'root',
  shouldForwardProp: props =>
    props !== 'tagging' &&
    props !== 'px' &&
    props !== 'py' &&
    props !== 'hovering' &&
    props !== 'forceShow'
})<{
  tagging?: boolean;
  px: number;
  py: number;
  hovering?: boolean;
  forceShow?: boolean;
}>(({ theme, tagging, px, py, hovering, forceShow }) => ({
  position: 'absolute',
  width: tagging ? 10 : 100,
  height: tagging ? 10 : 100,
  transform: 'translate(-50%,-50%)',
  '&:hover > div:first-of-type': {
    visibility: 'visible'
  },
  ...((tagging || forceShow) && {
    '& > div:first-of-type': {
      visibility: 'visible'
    }
  }),
  zIndex: hovering ? 10 : 'auto',
  ...(px &&
    py && {
      left: `${px}%`,
      top: `${py}%`
    })
}));

const Item = styled('div', { name, slot: 'Item' })(({ theme }) => ({
  position: 'absolute',
  backgroundColor:
    theme.palette.mode === 'dark' ? 'rgba(0,0,0,.6)' : 'rgba(255,255,255,.8)',
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  maxWidth: '120px',
  textAlign: 'center',
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(0.5),
  transform: 'translate(-50%,-50%)',
  left: '50%',
  top: '50%',
  visibility: 'hidden'
}));

const ButtonStyled = styled(Button, { name, slot: 'ButtonStyled' })(
  ({ theme }) => ({
    color:
      theme.palette.mode === 'light'
        ? theme.palette.grey['A700']
        : theme.palette.grey['800'],
    minWidth: '11px',
    padding: theme.spacing(0.5, 1),
    margin: theme.spacing(-0.5),
    fontSize: theme.mixins.pxToRem(11)
  })
);

const ItemLabel = styled('span', { name, slot: 'LabelStyled' })(
  ({ theme }) => ({
    margin: '0 4px'
  })
);

export type PhotoTagItemProps = {
  item: PhotoTagShape;
  user?: UserItemShape;
  onRemove: (evt: React.MouseEvent<unknown>, item: unknown) => void;
  tagging?: boolean;
  content: Record<string, any>;
  forceShow?: boolean;
} & PhotoTagShape;

export default function PhotoTag({
  content,
  px,
  py,
  tagging,
  onRemove,
  forceShow,
  user
}: PhotoTagItemProps) {
  const hasRemove = tagging;
  const [isHover, setIsHover] = React.useState(false);

  return (
    <Root
      px={px}
      py={py}
      tagging={tagging}
      hovering={isHover}
      forceShow={forceShow}
    >
      <Item
        onMouseEnter={() => setIsHover(true)}
        onMouseLeave={() => setIsHover(false)}
      >
        <ItemLabel>{content?.label || user?.full_name}</ItemLabel>
        {hasRemove ? (
          <ButtonStyled
            disableRipple
            disableFocusRipple
            onClick={e => onRemove(e, content?.id)}
          >
            <LineIcon icon="ico-close" />
          </ButtonStyled>
        ) : null}
      </Item>
    </Root>
  );
}

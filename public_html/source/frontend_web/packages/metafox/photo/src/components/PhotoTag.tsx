import { PhotoTagShape } from '@metafox/photo/types';
import { ItemExtraShape, LineIcon, UserName } from '@metafox/ui';
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
  maxWidth: '200px',
  textAlign: 'center',
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(0.5),
  transform: 'translate(-50%,-50%)',
  left: '50%',
  top: '50%',
  visibility: 'hidden'
}));
const LinkStyled = styled(UserName, { name, slot: 'LinkStyled' })(
  ({ theme }) => ({
    color:
      theme.palette.mode === 'light'
        ? theme.palette.grey['A700']
        : theme.palette.common.white,
    fontSize: theme.mixins.pxToRem(13),
    fontWeight: theme.typography.fontWeightBold,
    maxWidth: '180px',
    display: 'inline-block',
    whiteSpace: 'nowrap',
    textOverflow: 'ellipsis',
    overflow: 'hidden',
    flexGrow: 1,
    padding: theme.spacing(0, 0.5)
  })
);
const ButtonStyled = styled(Button, { name, slot: 'ButtonStyled' })(
  ({ theme }) => ({
    color:
      theme.palette.mode === 'light'
        ? theme.palette.grey['A700']
        : theme.palette.common.white,
    minWidth: '11px',
    padding: theme.spacing(0.5, 1),
    margin: theme.spacing(-0.5),
    fontSize: theme.mixins.pxToRem(11)
  })
);

export type PhotoTagItemProps = {
  item: PhotoTagShape;
  user?: UserItemShape;
  onRemove: (evt: React.MouseEvent<unknown>, item: unknown) => void;
  tagging?: boolean;
  extra: ItemExtraShape & {
    can_remove_tag_friend?: boolean;
    can_tag_friend?: boolean;
  };
  isTypePreview?: boolean;
  forceShow?: boolean;
} & PhotoTagShape;

const ItemLabel = styled('span', { name, slot: 'LabelStyled' })(
  ({ theme }) => ({
    margin: '0 4px'
  })
);

export default function PhotoTag({
  item,
  user,
  tagging,
  extra,
  onRemove,
  isTypePreview,
  forceShow
}: PhotoTagItemProps) {
  const hasRemove = tagging && item?.extra?.can_remove_tag_friend;
  const [isHover, setIsHover] = React.useState(false);

  return (
    <Root
      px={item.px}
      py={item.py}
      tagging={tagging}
      hovering={isHover}
      forceShow={forceShow}
    >
      <Item
        onMouseEnter={() => setIsHover(true)}
        onMouseLeave={() => setIsHover(false)}
      >
        {user && !isTypePreview ? (
          <LinkStyled
            user={user}
            hoverCard={false}
            rootProps={{ display: 'inherit' }}
          />
        ) : (
          <ItemLabel>{user?.full_name || item?.content}</ItemLabel>
        )}
        {hasRemove ? (
          <ButtonStyled
            disableRipple
            disableFocusRipple
            onClick={e => {
              onRemove(e, item.id);
            }}
          >
            <LineIcon icon="ico-close" />
          </ButtonStyled>
        ) : null}
      </Item>
    </Root>
  );
}

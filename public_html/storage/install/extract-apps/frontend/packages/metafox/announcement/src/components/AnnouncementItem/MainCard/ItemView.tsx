import { Link, useGlobal } from '@metafox/framework';
import { ItemSummary, ItemTitle, ItemView, LineIcon } from '@metafox/ui';
import { IconButton, styled } from '@mui/material';
import React from 'react';
import { AnnouncementItemProps } from '../../../types';
import { alpha } from '@mui/system/colorManipulator';

type Props = AnnouncementItemProps;
const name = 'announcement-item';

const ItemViewStyled = styled(ItemView, {
  name: 'ItemView',
  shouldForwardProp: prop => prop !== 'type'
})<{ type?: string }>(({ theme, type }) => ({
  padding: theme.spacing(1.5),
  paddingBottom: theme.spacing(2),
  borderRadius: theme.shape.borderRadius,
  backgroundColor: alpha(theme.palette.grey['300'], 0.15),
  ...(type === 'danger' && {
    backgroundColor: alpha(theme.palette.error.main, 0.15)
  }),
  ...(type === 'success' && {
    backgroundColor: alpha(theme.palette.success.main, 0.15)
  }),
  ...(type === 'warning' && {
    backgroundColor: alpha(theme.palette.warning.main, 0.15)
  }),
  ...(type === 'info' && {
    backgroundColor: alpha(theme.palette.info.main, 0.15)
  }),
  '&:hover': {
    '#button-remove': {
      visibility: 'visible'
    }
  }
}));

const Description = styled('span')(({ theme }) => ({
  flex: 'unset',
  marginTop: 'unset',
  '[dir="rtl"] &': {
    '& span': {
      display: 'inline-block'
    }
  }
}));
const Total = styled('div')(({ theme }) => ({
  display: 'flex',
  position: 'relative',
  '& div:first-of-type p': {
    fontWeight: 'bold'
  }
}));

const LinkStyled = styled(Link, {
  name,
  slot: 'Link',
  shouldForwardProp: prop => prop !== 'type'
})<{ type?: string }>(({ theme, type }) => ({
  ...(type === 'danger' && {
    color:
      theme.palette.mode === 'light'
        ? theme.palette.error.dark
        : theme.palette.error.light
  }),
  ...(type === 'success' && {
    color:
      theme.palette.mode === 'light'
        ? theme.palette.success.dark
        : theme.palette.success.light
  }),
  ...(type === 'warning' && {
    color:
      theme.palette.mode === 'light'
        ? theme.palette.warning.dark
        : theme.palette.warning.light
  }),
  ...(type === 'info' && {
    color:
      theme.palette.mode === 'light'
        ? theme.palette.info.dark
        : theme.palette.info.light
  })
}));

const ButtonClose = styled(IconButton, {
  name,
  slot: 'buttonClose',
  shouldForwardProp: prop => prop !== 'typeColor' && prop !== 'isTablet'
})<{ typeColor?: string; isTablet?: boolean }>(
  ({ theme, typeColor, isTablet }) => ({
    position: 'absolute',
    right: '4px',
    cursor: 'pointer',
    fontSize: theme.mixins.pxToRem(16),
    ...(typeColor === 'danger' && {
      color: theme.palette.error.main
    }),
    ...(typeColor === 'success' && {
      color: theme.palette.success.main
    }),
    ...(typeColor === 'warning' && {
      color: theme.palette.warning.main
    }),
    ...(typeColor === 'info' && {
      color: theme.palette.info.main
    }),
    backgroundColor: theme.palette.action.hover,
    '&:hover': {
      cursor: 'pointer',
      backgroundColor: theme.palette.action.hover
    },
    ...(!isTablet && {
      visibility: 'hidden'
    })
  })
);

export default function AnnouncementItemView({
  item,
  wrapAs,
  wrapProps,
  actions
}: Props) {
  const { useIsMobile } = useGlobal();
  const isTablet = useIsMobile(true);
  const [disableClose, setDisableClose] = React.useState(false);

  if (!item) return null;

  const { link: to, extra } = item;

  const onClose = () => {
    setDisableClose(true);
    actions.closeAnnouncement(() => setDisableClose(false));
  };

  return (
    <ItemViewStyled
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={'itemAnnouncement'}
      type={item?.style}
    >
      <ItemTitle
        role="button"
        mt={1}
        mr={3}
        color={theme => ({
          ...(item?.style === 'danger' && {
            color:
              theme.palette.mode === 'light'
                ? theme.palette.error.dark
                : theme.palette.error.main
          }),
          ...(item?.style === 'success' && {
            color:
              theme.palette.mode === 'light'
                ? theme.palette.success.dark
                : theme.palette.success.main
          }),
          ...(item?.style === 'warning' && {
            color:
              theme.palette.mode === 'light'
                ? theme.palette.warning.dark
                : theme.palette.warning.main
          }),
          ...(item?.style === 'info' && {
            color:
              theme.palette.mode === 'light'
                ? theme.palette.info.dark
                : theme.palette.info.main
          })
        })}
      >
        <LinkStyled to={to} asModal type={item?.style}>
          {item?.title}
        </LinkStyled>
      </ItemTitle>
      <Total>
        <Link
          to={to}
          asModal
          sx={{
            position: 'absolute',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            zIndex: 2
          }}
        />
        <Description>
          <ItemSummary
            style={{ fontWeight: 'normal' }}
            color={theme => ({
              ...(item?.style === 'danger' && {
                color:
                  theme.palette.mode === 'light'
                    ? theme.palette.error.dark
                    : theme.palette.error.main
              }),
              ...(item?.style === 'success' && {
                color:
                  theme.palette.mode === 'light'
                    ? theme.palette.success.dark
                    : theme.palette.success.main
              }),
              ...(item?.style === 'warning' && {
                color:
                  theme.palette.mode === 'light'
                    ? theme.palette.warning.dark
                    : theme.palette.warning.main
              }),
              ...(item?.style === 'info' && {
                color:
                  theme.palette.mode === 'light'
                    ? theme.palette.info.dark
                    : theme.palette.info.main
              })
            })}
          >{`${item?.description}`}</ItemSummary>
        </Description>
      </Total>
      {extra?.can_close && (
        <ButtonClose
          size="small"
          onClick={onClose}
          disabled={disableClose}
          typeColor={item?.style}
          id="button-remove"
          isTablet={isTablet}
        >
          <LineIcon icon="ico-close" />
        </ButtonClose>
      )}
    </ItemViewStyled>
  );
}

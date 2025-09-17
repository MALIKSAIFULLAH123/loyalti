import { RefOf } from '@metafox/framework';
import { TruncateTextProps } from '@metafox/ui';
import { Box, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import getSafariVersion from './getSafariVersion';

const TruncateTextRoot = styled(Box, {
  name: 'MuiTruncateText',
  slot: 'Root',
  shouldForwardProp(prop: string) {
    return prop !== 'lines' && prop !== 'fixHeight' && prop !== 'showFull';
  }
})<TruncateTextProps>(({ theme }) => ({
  display: 'block',
  maxWidth: '100%'
}));

const TruncateText = styled(Typography, {
  name: 'MuiTruncateText',
  slot: 'Content',
  shouldForwardProp(prop: string) {
    return (
      prop !== 'lines' &&
      prop !== 'fixHeight' &&
      prop !== 'showFull' &&
      prop !== 'notSupportLineClamp' &&
      prop !== 'isSafari'
    );
  }
})<TruncateTextProps>(
  ({
    theme,
    lines,
    variant,
    fixHeight,
    notSupportLineClamp,
    isSafari,
    showFull
  }) => ({
    display: 'block',
    ...(lines > 1 && {
      display: '-webkit-box',
      padding: '0',
      overflow: 'hidden',
      maxWidth: '100%',
      whiteSpace: 'normal',
      textOverflow: 'ellipsis',
      WebkitBoxOrient: 'vertical'
    }),
    // eslint-disable-next-line eqeqeq
    ...(lines == 1 && {
      overflow: 'hidden',
      textOverflow: 'ellipsis',
      whiteSpace: 'nowrap',
      maxWidth: '100%'
    }),
    ...(lines > 1 &&
      theme.typography[variant] && {
        WebkitLineClamp: lines
      }),
    ...(lines > 1 &&
      theme.typography[variant] &&
      fixHeight && {
        height: `calc(${theme.typography[variant].lineHeight} * ${theme.typography[variant].fontSize} * ${lines})`
      }),
    ...(lines > 1 &&
      theme.typography[variant] &&
      notSupportLineClamp === true && {
        // eslint-disable-next-line max-len
        maxHeight: `calc(${theme.typography[variant].lineHeight} * ${theme.typography[variant].fontSize} * ${lines} - (${theme.typography[variant].lineHeight} * ${theme.typography[variant].fontSize} * 0.05))`,
        '& *': {
          marginTop: '0 !important',
          marginBottom: '0 !important',
          paddingTop: '0 !important',
          paddingBottom: '0 !important'
        }
      }),
    ...(isSafari &&
      !showFull && {
        '& > *': {
          display: 'inline'
        },
        '& > .MuiSkeleton-root': {
          display: 'block'
        },
        ...(notSupportLineClamp === true && {
          'p:empty': {
            height: '0 !important'
          }
        })
      })
  })
);

export default React.forwardRef<HTMLElement, TruncateTextProps>(
  (
    {
      lines = 2,
      fixHeight = false,
      variant = 'body1',
      className,
      sx,
      showFull,
      children,
      ...rest
    }: TruncateTextProps,
    ref: RefOf<HTMLElement>
  ) => {
    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

    const safariVer = getSafariVersion();

    const notSupportLineClamp =
      window.navigator.userAgent.includes('MSIE') ||
      (isSafari && safariVer <= 17);

    return (
      <TruncateTextRoot className={className} sx={sx}>
        <TruncateText
          variant={variant}
          lines={showFull ? 0 : lines}
          showFull={showFull}
          ref={ref}
          fixHeight={fixHeight}
          notSupportLineClamp={notSupportLineClamp}
          isSafari={isSafari}
          component="div"
          {...rest}
        >
          {children}
        </TruncateText>
      </TruncateTextRoot>
    );
  }
);

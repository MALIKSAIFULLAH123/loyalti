import {
  GridItemDividerVariant,
  GridItemHoverVariant,
  GridItemVariant,
  ItemSelectedVariant,
  MediaPlacementVariant,
  RefOf,
  useGlobal
} from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import generateUtilityClasses from '@mui/base/generateUtilityClasses';
import { Box, Grid, styled } from '@mui/material';
import { BoxProps } from '@mui/system';
import { camelCase } from 'lodash';
import React from 'react';

export interface ItemViewClassName {
  mediaStart: string;
  mediaEnd: string;
  mediaTop: string;
  mediaBottom: string;
  dividerBottomFull: string;
  dividerTopFull: string;
}

export interface MuiItemViewProps {
  mediaPlacement?: MediaPlacementVariant;
  variant?: GridItemVariant;
  selectedVariant?: ItemSelectedVariant;
  hoverVariant?: GridItemHoverVariant;
  dividerVariant?: GridItemDividerVariant;
}

const mediaClasses = generateUtilityClasses('media', [
  'start',
  'end',
  'top',
  'bottom'
]);

const ItemView = styled(Box, {
  name: 'ItemView',
  slot: 'Root',
  skipSx: true,
  shouldForwardProp(prop) {
    return (
      prop !== 'mediaPlacement' &&
      prop !== 'selectedVariant' &&
      prop !== 'hoverVariant' &&
      prop !== 'dividerVariant' &&
      prop !== 'variant' &&
      prop !== 'hover' &&
      prop !== 'divider' &&
      prop !== 'minHeight'
    );
  },
  overridesResolver: ({ mediaPlacement, variant }, styles) => [
    styles.root,
    mediaPlacement && styles[mediaClasses[mediaPlacement]],
    variant && styles[variant]
  ]
})<MuiItemViewProps & BoxProps & { selected?: boolean; onClick?: any }>(
  ({
    theme,
    minHeight,
    mediaPlacement,
    variant,
    dividerVariant,
    selectedVariant,
    selected,
    hoverVariant
  }) => ({
    ...{
      minHeight: minHeight > 0 ? `${minHeight}px` : undefined,
      overflow: 'hidden',
      position: 'relative',
      maxWidth: '100%',
      flexBasis: '100%',
      boxSizing: 'border-box',
      height: '100%',
      '&:hover': {
        '& .ItemView-action': {
          opacity: 1
        }
      }
    },
    ...(mediaPlacement === 'start' && {
      display: 'flex',
      flexDirection: 'row'
    }),
    ...(mediaPlacement === 'end' && {
      display: 'flex',
      flexDirection: 'row-reverse'
    }),
    ...(mediaPlacement === 'top' && {
      display: 'flex',
      flexDirection: 'column'
    }),
    ...(mediaPlacement === 'bottom' && {
      display: 'flex',
      flexDirection: 'column-reverse'
    }),
    ...('flattedCard' === variant && {
      borderRadius: theme.shape.borderRadius,
      backgroundColor: theme.palette.background.paper
    }),
    ...('containedCard' === variant && {
      borderRadius: theme.shape.borderRadius,
      backgroundColor: theme.palette.background.paper
    }),
    ...('flatted' === variant && {
      borderRadius: theme.shape.borderRadius,
      backgroundColor: theme.palette.background.paper
    }),
    ...('contained' === variant && {
      borderRadius: theme.shape.borderRadius
    }),
    ...('background' === selectedVariant &&
      selected && {
        transition: 'background-color 300ms ease',
        backgroundColor: theme.palette.action.hover
      }),
    ...('background' === hoverVariant &&
      !selected && {
        transition: 'background-color 300ms ease',
        ':hover': {
          backgroundColor: theme.palette.action.hover
        }
      }),
    ...(dividerVariant === 'top' && {
      borderTop: theme.mixins.border('secondary')
    }),
    ...(dividerVariant === 'bottom' && {
      borderBottom: theme.mixins.border('secondary')
    }),
    ...(dividerVariant === 'divider' && {
      borderBottom: theme.mixins.border('secondary'),
      '.MuiGrid-item:last-child > &': {
        borderBottom: 0
      }
    })
  })
);

export type Props = {
  selected?: boolean;
  wrapProps?: any;
  wrapAs: React.FC<any>;
  testid: string;
  identity?: string;
  identityTracking?: string;
} & BoxProps &
  React.HTMLAttributes<HTMLDivElement>;

const defaultWrapProps = { xs: 12 };

export default React.forwardRef<HTMLDivElement, Props>(
  (
    {
      children,
      wrapAs: WrapAs = Grid,
      wrapProps = defaultWrapProps,
      testid,
      identity,
      identityTracking,
      ...rest
    }: Props,
    ref: RefOf<HTMLDivElement>
  ) => {
    const { itemProps } = useBlock();
    const { InViewTrackingSponsor } = useGlobal();

    // prevent children check
    if (!itemProps) return null;

    const { content, media } = itemProps;
    const tid =
      testid === 'loadingIndicator'
        ? 'loadingIndicator'
        : camelCase(`item ${testid}`);
    const identityTrackingView = identityTracking ?? identity;

    return (
      <WrapAs item {...wrapProps} ref={ref} data-testid={tid}>
        {identityTrackingView && (
          <InViewTrackingSponsor identity={identityTrackingView} />
        )}
        <ItemView
          mediaPlacement={media?.placement}
          data-testid={camelCase(`ItemView ${tid}`)}
          {...content}
          {...rest}
        >
          {children}
        </ItemView>
      </WrapAs>
    );
  }
);

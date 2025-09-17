import { Box, Grid } from '@mui/material';
import { styled } from '@mui/material/styles';
import { toClassName } from '@metafox/utils';

export interface SlotProps {
  minHeight?: string;
  maxWidth?: string;
  minWidth?: string;
  flexWeight?: string | number;
  useFlex?: boolean;
  fixed?: boolean;
  name?: string;
}

export interface SlotOuterProps {
  minHeight?: string;
  maxWidth?: string;
  minWidth?: string;
  liveEdit?: boolean;
  fixed?: boolean;
  controller?: boolean;
  sticky?: string;
  name?: string;
}
const name = 'LayoutSlot';

export const Slot = styled(Grid, {
  name,
  slot: 'root',
  shouldForwardProp: prop =>
    prop !== 'maxWidth' &&
    prop !== 'minWidth' &&
    prop !== 'minHeight' &&
    prop !== 'flexWeight' &&
    prop !== 'name' &&
    prop !== 'useFlex',
  overridesResolver: (props, styles) => [
    styles.content,
    styles[toClassName('root', props.name)]
  ]
})<SlotProps>(
  ({ theme, maxWidth, minWidth, minHeight, flexWeight, useFlex }) => ({
    display: 'block',
    flexBasis: '100%',
    position: 'relative',
    ...(minHeight === 'screen' && {
      minHeight: '100vh'
    }),
    ...(minHeight &&
      minHeight !== 'screen' && {
        minHeight
      }),
    ...(maxWidth && {
      maxWidth: `${theme.layoutSlot.points[maxWidth]}px !important`
    }),
    ...(minWidth && {
      minWidth: `${theme.layoutSlot.points[minWidth]}px !important`
    }),
    ...(useFlex && {
      flex: flexWeight ?? 1,
      minWidth: 0
    })
  })
);

export const SlotStage = styled(Box, {
  name,
  slot: 'stage',
  shouldForwardProp: (prop: string) =>
    prop !== 'maxWidth' &&
    prop !== 'minWidth' &&
    prop !== 'minHeight' &&
    prop !== 'controller' &&
    prop !== 'sticky' &&
    prop !== 'name' &&
    prop !== 'liveEdit',
  overridesResolver: (props, styles) => [
    styles.stage,
    styles[toClassName('stage', props.name)]
  ]
})<SlotOuterProps>(
  ({
    theme,
    minHeight,
    fixed,
    maxWidth,
    minWidth,
    liveEdit,
    controller,
    sticky
  }) => ({
    display: 'block',
    flexBasis: '100%',
    ...(fixed && {
      position: 'fixed'
    }),
    ...(minHeight === 'screen' && {
      minHeight: '100vh'
    }),
    ...(maxWidth && {
      maxWidth: theme.layoutSlot.points[maxWidth]
    }),
    ...(minWidth && {
      maxWidth: theme.layoutSlot.points[minWidth]
    }),
    ...(liveEdit && {
      position: 'relative',
      minHeight: theme.spacing(6)
    }),
    ...(controller && {
      position: 'relative',
      marginBottom: theme.spacing(1),
      padding: theme.spacing(1),
      minHeight: theme.spacing(8),
      borderColor: theme.palette.text.primary,
      borderStyle: 'dotted',
      borderWidth: 1
    }),
    ...(sticky === 'static' && {
      position: 'sticky',
      top: 0
    }),
    ...(sticky === 'dynamic' && {
      position: 'sticky'
    })
  })
);

export const PreviewSlot = styled(Box, {
  name,
  slot: 'preview',
  shouldForwardProp: prop => prop !== 'name'
})<{ name?: string }>(({ name, theme }) => ({
  fontSize: '0.8125rem',
  fontWeight: theme.typography.fontWeightMedium,
  height: 80,
  textTransform: 'lowercase',
  alignItems: 'center',
  justifyContent: 'center',
  display: 'flex'
}));

export const SlotContent = styled(Box, {
  name,
  slot: 'Content',
  shouldForwardProp: (prop: string) =>
    prop !== 'maxWidth' &&
    prop !== 'minWidth' &&
    prop !== 'minHeight' &&
    prop !== 'name' &&
    prop !== 'fixed',
  overridesResolver: (props, styles) => [
    styles.content,
    styles[toClassName('content', props.name)]
  ]
})<SlotOuterProps>(({ theme, minHeight, maxWidth, minWidth }) => ({
  display: 'block',
  flexBasis: '100%',
  marginLeft: 'auto',
  marginRight: 'auto',
  ...(minHeight === 'screen' && {
    minHeight: '100vh'
  }),
  ...(maxWidth && {
    maxWidth: theme.layoutSlot.points[maxWidth]
  }),
  ...(minWidth && {
    maxWidth: theme.layoutSlot.points[minWidth]
  })
}));

export const FixedStage = styled('div', {
  name,
  slot: 'fixedStage',
  overridesResolver(props, styles) {
    return [styles.fixedStage];
  }
})({
  display: 'flex',
  height: '100%',
  flexDirection: 'column'
});

export const FixedContent = styled('div', {
  name,
  slot: 'fixedContent',
  overridesResolver(props, styles) {
    return [styles.fixedContent];
  }
})({
  flex: 1,
  minHeight: 0
});

export const FixedHeader = styled('div', {
  name,
  slot: 'fixedHeader',
  overridesResolver(props, styles) {
    return [styles.fixedHeader];
  }
})({});

export const FixedFooter = styled('div', {
  name,
  slot: 'fixedFooter',
  overridesResolver(props, styles) {
    return [styles.fixedFooter];
  }
})({});

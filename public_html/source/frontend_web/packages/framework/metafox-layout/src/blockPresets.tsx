import { BlockStyleProps, ComponentSpacingProps } from '@metafox/framework';

const spacing: ComponentSpacingProps = {
  pl: 0,
  pt: 0,
  pb: 0,
  pr: 0,
  ml: 0,
  mr: 0,
  mt: 0,
  mb: 0
};

const blockPresets: Record<string, Partial<BlockStyleProps>> = {
  Plained: {
    titleComponent: 'h2',
    titleVariant: 'subtitle1',
    titleColor: 'textPrimary',
    blockStyle: { ...spacing },
    headerStyle: {
      ...spacing,
      pt: 1,
      pb: 1
    },
    contentStyle: {
      pt: 1,
      pb: 1
    },
    footerStyle: { ...spacing }
  },
  Contained: {
    titleComponent: 'h2',
    titleVariant: 'subtitle1',
    titleColor: 'textPrimary',
    blockStyle: {
      ...spacing,
      mb: 2,
      bgColor: 'paper',
      borderRadius: 'base'
    },
    headerStyle: {
      ...spacing,
      pl: 2,
      pt: 1,
      pr: 2,
      pb: 1
    },
    contentStyle: {
      pl: 2,
      pt: 2,
      pb: 2,
      pr: 2
    },
    footerStyle: {
      ...spacing,
      pl: 2,
      pt: 1,
      pr: 2,
      pb: 1
    }
  },
  Blocker: {
    blockStyle: {
      mb: 2
    },
    contentStyle: {
      pt: 2,
      pb: 2,
      pl: 2,
      pr: 2,
      bgColor: 'paper',
      borderRadius: 'base'
    },
    headerStyle: {
      pt: 2,
      pb: 2
    },
    footerStyle: {}
  },
  Search: {
    variant: 'blocker',
    titleComponent: 'h2',
    titleVariant: 'subtitle1',
    titleColor: 'text',
    noFooter: true,
    noHeader: true,
    blockStyle: {
      pt: 2,
      mb: 2
    },
    contentStyle: {},
    headerStyle: {},
    footerStyle: {}
  },
  'Admin Table': {
    titleComponent: 'h2',
    titleVariant: 'secondary',
    titleColor: 'text',
    noFooter: true,
    noHeader: false,
    blockStyle: {
      mb: 2
    },
    contentStyle: {
      ml: 2,
      mt: 2,
      mr: 2,
      bgColor: 'paper',
      borderRadius: 'base'
    },
    headerStyle: {
      pl: 2,
      pt: 3,
      pr: 2,
      pb: 1,
      dividerVariant: '0'
    }
  },
  'Admin Form': {
    variant: 'Admin Form',
    titleComponent: 'h2',
    titleVariant: 'secondary',
    titleColor: 'text',
    noFooter: true,
    noHeader: true,
    blockStyle: {
      mb: 2
    },
    contentStyle: {
      pl: 2,
      pt: 2,
      pr: 2,
      pb: 2,
      ml: 2,
      mt: 2,
      mr: 2,
      bgColor: 'paper',
      borderRadius: 'base'
    },
    headerStyle: {
      pl: 2,
      pt: 3,
      pr: 2,
      pb: 1,
      dividerVariant: '0'
    },
    footerStyle: {}
  }
};

export default blockPresets;

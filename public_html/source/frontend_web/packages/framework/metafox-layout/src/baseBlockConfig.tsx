import { BlockStyleProps, ComponentSpacingProps } from '@metafox/framework';

const spacing: ComponentSpacingProps = {
  pl: 0,
  pt: 0,
  pr: 0,
  pb: 0,
  ml: 0,
  mt: 0,
  mr: 0,
  mb: 0
};

const style = {
  ...spacing,
  bgColor: '0',
  borderStyle: '0',
  borderColor: '0'
};

const baseBlockConfig: {
  blockProps: BlockStyleProps;
  title: string;
} = {
  title: '',
  blockProps: {
    variant: 'plained',
    titleComponent: 'h2',
    titleVariant: 'subtitle1',
    titleColor: 'text',
    noFooter: true,
    noHeader: true,
    padding: spacing,
    gutter: spacing,
    contentGutter: spacing,
    headerGutter: spacing,
    footerGutter: spacing,
    // update style
    blockStyle: style,
    contentStyle: style,
    headerStyle: style,
    footerStyle: style
  }
};

export default baseBlockConfig;

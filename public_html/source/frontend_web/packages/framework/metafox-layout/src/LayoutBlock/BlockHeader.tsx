/**
 * @type: ui
 * name: ui.block.default.header
 */
import { useGlobal } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import { Box } from '@mui/material';
import { SxProps, styled } from '@mui/material/styles';
import React from 'react';
import BlockDivider from './BlockDivider';
import BlockTitle from './BlockTitle';
import HeaderActions from './HeaderActions';
import SearchBox from './SearchBox';

const BlockHeader = styled(Box, {
  name: 'LayoutBlock',
  slot: 'Header',
  shouldForwardProp: prop =>
    prop !== 'dividerVariant' && prop !== 'borderStyle' && prop !== 'bgColor',
  overridesResolver(props, styles) {
    return [styles.header];
  }
})(({ theme }) => ({
  position: 'relative',
  display: 'flex',
  flexDirection: 'row',
  boxSizing: 'border-box',
  alignItems: 'center'
}));

export type Props = {
  isEmpty?: boolean;
  title?: string;
  children?: React.ReactNode;
  linkViewAll?: string;
  data?: any[];
  headerActionsStyle?: SxProps;
};

export default function BlockHeaderRoot({ title, children, data }: Props) {
  const {
    noHeader,
    hasSearchBox,
    placeholderSearchBox,
    onQueryChange,
    headerActionsStyle,
    headerActions: headerActionsProps,
    headerActionsResource,
    blockProps: { headerStyle } = {}
  } = useBlock();
  const { i18n, useGetActionHeader } = useGlobal();
  const headerActionsResourceData = useGetActionHeader(headerActionsResource);
  const headerActions = headerActionsResourceData || headerActionsProps;

  if (noHeader) return null;

  if (!children && !title) return null;

  if (!children && title) {
    if (typeof title === 'string') {
      children = (
        <BlockTitle data-testid="blockHeader">
          {i18n.formatMessage({ id: title })}
        </BlockTitle>
      );
    } else {
      children = <BlockTitle data-testid="blockHeader">{title}</BlockTitle>;
    }
  }

  return (
    <>
      <>
        <BlockHeader data-testid="blockHeader" {...headerStyle}>
          {children}
          {headerActions ? (
            <HeaderActions
              actions={headerActions}
              data={data}
              sx={headerActionsStyle}
            />
          ) : null}
        </BlockHeader>
        {hasSearchBox ? (
          <>
            <BlockDivider variant={headerStyle?.dividerVariant} />
            <Box
              data-testid="blockHeader"
              sx={{
                display: 'flex',
                justifyContent: 'flex-end',
                m: 2,
                mb: 0
              }}
            >
              <SearchBox
                placeholder={placeholderSearchBox}
                value=""
                onQueryChange={onQueryChange}
                sx={{ width: { sm: 'auto', xs: '100%' } }}
              />
            </Box>
          </>
        ) : null}
      </>
      {hasSearchBox ? null : (
        <BlockDivider variant={headerStyle?.dividerVariant} />
      )}
    </>
  );
}

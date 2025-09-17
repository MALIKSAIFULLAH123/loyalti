/**
 * @type: service
 * name: LayoutSection
 * chunkName: boot
 */

import { useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import { EditMode } from '../types';
import React from 'react';
import BlockHeader from '../LayoutBlock/BlockHeader';

export type LayoutSectionProps = {
  sectionName: 'content' | 'header' | 'footer';
  layoutEditMode: EditMode;
  elements?: any[];
};

const Root = styled(Box, {
  name: 'LayoutSection',
  slot: 'EditingRoot',
  shouldForwardProp: prop => prop !== 'sectionName'
})<{ sectionName: string }>(({ sectionName, theme }) => ({
  padding: theme.spacing(0, 2),
  ...(sectionName === 'content' && {
    minHeight: 200
  }),
  ...(sectionName === 'header' && {
    minHeight: 180
  }),
  ...(sectionName === 'footer' && {
    minHeight: 180
  })
}));

const Wrapper = styled(Box, { slot: 'Wrapper' })(({ theme }) => ({
  maxWidth: 1280, 
  marginLeft: 'auto',
  marginRight: 'auto',
  marginTop: theme.spacing(2),
  padding: theme.spacing(2),
  borderWidth: 1,
  borderStyle: 'dashed',
  borderColor: theme.palette.default.main
}));

const Title = styled('h2', { slot: 'Title' })(({ theme }) => ({
  fontSize: 18,
  fontWeight: 700,
  paddingBottom: theme.spacing(1),
  marginTop: theme.spacing(0),
  marginBottom: theme.spacing(2),
  textTransform: 'capitalize',
  borderBottom: '1px solid'
}));

export default function LayoutSection({
  elements,
  sectionName,
  layoutEditMode
}: LayoutSectionProps) {
  const { jsxBackend } = useGlobal();

  if (!elements?.length) {
    return null;
  }

  if (
    layoutEditMode === EditMode.editLayout ||
    layoutEditMode === EditMode.editPageContent ||
    layoutEditMode === EditMode.editSiteContent
  ) {
    return (
      <Root sectionName={sectionName}>
        <Wrapper>
          <BlockHeader>
            <Title>{sectionName}</Title>
          </BlockHeader>
          {jsxBackend.render(elements)}
        </Wrapper>
      </Root>
    );
  }

  return jsxBackend.render(elements);
}

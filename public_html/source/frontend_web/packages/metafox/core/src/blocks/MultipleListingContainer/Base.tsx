import { useGlobal } from '@metafox/framework';
import { UIBlockViewProps } from '@metafox/ui';
import { isArray } from 'lodash';
import { Box } from '@mui/material';
import React from 'react';
import EmptyListView from './EmptyListView';

type Props = UIBlockViewProps & {
  emptyPage?: string;
  emptyPageProps?: Record<string, any>;
};
export default function ListContainer({
  elements,
  emptyPage,
  emptyPageProps
}: Props) {
  const { jsxBackend } = useGlobal();

  if (!isArray(elements) || !elements.length) return null;

  const NoResultsBlock = jsxBackend.get(emptyPage);

  return (
    <Box>
      {jsxBackend.render(elements)}
      <EmptyListView>
        <NoResultsBlock {...emptyPageProps} />
      </EmptyListView>
    </Box>
  );
}

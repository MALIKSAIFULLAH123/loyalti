/**
 * @type: ui
 * name: ui.SortActivityFeed
 * chunkName: feed
 */

import { UIBlockViewProps } from '@metafox/ui';
import React from 'react';
import { useResourceForm } from '@metafox/framework';
import { FormBuilder } from '@metafox/form';
import { APP_FEED, RESOURCE_FEED } from '@metafox/feed/constant';
import { styled } from '@mui/material/styles';
import { Box } from '@mui/material';
import { FormikHelpers } from 'formik';

const name = 'SortActivityFeed';

const RootStyled = styled(Box, { name, slot: 'RootStyled' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'flex-end',
  alignItems: 'center'
}));
const SortWrapperStyled = styled(Box, { name, slot: 'SortWrapperStyled' })(
  ({ theme }) => ({
    display: 'inline-block',
    position: 'relative',
    zIndex: 9,
    overflow: 'hidden'
  })
);

export interface Props extends UIBlockViewProps {
  onSubmit: (
    values: Record<string, any>,
    form: FormikHelpers<any>
  ) => void | Promise<any>;
  sort: string;
}

export default function SortActivityFeed(props: Props) {
  const { onSubmit, sort } = props || {};

  const formSchema = useResourceForm(APP_FEED, RESOURCE_FEED, 'sort');

  if (!formSchema) return null;

  return (
    <RootStyled>
      <SortWrapperStyled>
        <FormBuilder
          key={sort}
          noHeader
          noBreadcrumb
          formSchema={formSchema}
          onSubmit={onSubmit}
          pageParams={{ sort }}
          debounceSubmitTime={0}
        />
      </SortWrapperStyled>
    </RootStyled>
  );
}

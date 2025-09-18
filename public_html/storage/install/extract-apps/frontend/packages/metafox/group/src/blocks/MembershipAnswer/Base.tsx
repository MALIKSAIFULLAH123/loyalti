import {
  BlockViewProps,
  useGlobal,
  useResourceAction,
  useResourceForm
} from '@metafox/framework';
import { styled, Box } from '@mui/material';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import { FormBuilder } from '@metafox/form';
import { whenParamRules } from '@metafox/utils';
import qs from 'querystring';
import { APP_GROUP, GROUP_REQUEST } from '@metafox/group/constant';
import { SmartDataGrid } from '@metafox/ui/Loadable';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';

export type Props = BlockViewProps;

const ContentWrapper = styled(Box, {
  name: 'ContentWrapper'
})(({ theme }) => ({
  padding: theme.spacing(3, 2, 2),
  [theme.breakpoints.down('md')]: {
    padding: theme.spacing(0)
  }
}));

const GridWrapper = styled(Box, {
  name: 'GridWrapper'
})(({ theme }) => ({}));

export default function Base({ title, ...rest }: Props) {
  const { navigate } = useGlobal();

  const dataSource = useResourceAction(APP_GROUP, GROUP_REQUEST, 'getGrid');

  const formSchema = useResourceForm(APP_GROUP, GROUP_REQUEST, 'search');

  const submitFilter = (values, form) => {
    const apiRules = dataSource.apiRules;

    const params = whenParamRules(values, apiRules);

    navigate(`?${qs.stringify(params)}`, { replace: true });
    form.setSubmitting(false);
  };

  return (
    <Block testid="groupRequestBlock" {...rest}>
      <BlockHeader title={title}></BlockHeader>
      <BlockContent {...rest}>
        <ContentWrapper>
          <FormBuilder
            navigationConfirmWhenDirty={false}
            formSchema={formSchema}
            onSubmit={submitFilter}
          />
          <GridWrapper>
            <SmartDataGrid
              dataSource={dataSource}
              gridName={'group.group_request'}
              errorComponent={ErrorBoundary}
            />
          </GridWrapper>
        </ContentWrapper>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'GroupRequestBlock';

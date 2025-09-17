import { RemoteDataSource } from '@metafox/framework';
import {
  FormSchemaShape,
  RemoteFormBuilderProps,
  SmartFormBuilder
} from '@metafox/form';
import { Box } from '@mui/material';
import React from 'react';

interface Props {
  dataSource?: RemoteDataSource;
  visibility?: boolean;
  formSchema: FormSchemaShape;
  hideTitle?: boolean;
  hideDescription?: boolean;
  searchValues: Record<string, any>;
  onSearchChange: (values: any, form: any) => void;
}

const loadingComponent = (
  props: RemoteFormBuilderProps['loadingComponent']
) => <div />;

export default function GridSearchForm({
  visibility,
  hideTitle,
  hideDescription,
  onSearchChange,
  dataSource,
  formSchema,
  searchValues,
  ...rest
}: Props) {
  if (dataSource || formSchema) {
    return (
      <Box
        sx={{
          display: visibility ? undefined : 'hidden',
          padding: hideTitle ? '12px 8px 0 16px' : undefined
        }}
      >
        <SmartFormBuilder
          loadingComponent={loadingComponent}
          initialValues={searchValues || {}}
          noHeader
          dataSource={dataSource}
          onSubmit={onSearchChange}
          formSchema={formSchema}
          navigationConfirmWhenDirty={false}
          {...rest}
        />
      </Box>
    );
  }

  return null;
}

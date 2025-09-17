import React from 'react';
import { styled } from '@mui/material/styles';
import { FormSchemaShape, SmartFormBuilder } from '@metafox/form';
import { RemoteDataSource } from '@metafox/framework';
import { isEmpty } from 'lodash';

const RootStyled = styled('div', {
  name: 'RootStyled'
})<{}>(({ theme }) => ({
  padding: theme.spacing(2),
  marginBottom: theme.spacing(1.5),
  border: '1px solid #eeeeee',
  '& > div': {
    width: '100%'
  }
}));
interface IProps {
  initialValues?: any;
  formSchema?: FormSchemaShape;
  dataSource?: RemoteDataSource;
  onChange?: (valueField: any, fieldName: string) => void;
}

const getFieldChange = (originValues, newValues) => {
  const fieldChange = {};

  if (isEmpty(newValues)) return;

  Object.keys(originValues).forEach(key => {
    if (newValues[key] !== originValues[key]) {
      fieldChange[key] =
        typeof newValues[key] === 'string'
          ? newValues[key].trim()
          : newValues[key];
    }
  });

  return fieldChange;
};

function EditItemForm({
  initialValues,
  formSchema,
  dataSource,
  onChange
}: IProps) {
  const handleChange = React.useCallback(
    form => {
      const fieldChange = getFieldChange(initialValues, form?.values);

      if (isEmpty(fieldChange)) return;

      for (const key in fieldChange) {
        if (Object.prototype.hasOwnProperty.call(fieldChange, key)) {
          onChange(fieldChange?.[key], key);
        }
      }
    },
    [onChange, initialValues]
  );

  if (isEmpty(formSchema) && isEmpty(dataSource)) return;

  return (
    <RootStyled>
      <SmartFormBuilder
        noHeader
        noTitle
        initialValues={initialValues}
        formSchema={formSchema}
        dataSource={dataSource}
        onChange={handleChange}
      />
    </RootStyled>
  );
}

export default EditItemForm;

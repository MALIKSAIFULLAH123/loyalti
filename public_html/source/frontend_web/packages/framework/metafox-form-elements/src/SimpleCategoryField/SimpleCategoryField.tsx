/**
 * @type: formElement
 * name: form.element.SimpleCategory
 * chunkName: formExtras
 */
import { FormFieldProps } from '@metafox/form/types';
import { Box, Divider, Typography, styled } from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import * as React from 'react';
import ItemView from './ItemView';
import useStyles from './styles';

const name = 'SimpleCategoryField';

const BoxDivider = styled(Box, {
  name,
  slot: 'boxDivider',
  overridesResolver(props, styles) {
    return [styles.boxDivider];
  }
})(({ theme }) => ({
  paddingTop: theme.spacing(2),
  paddingBottom: theme.spacing(2)
}));

const TitleCategoryField = styled(Typography, {
  name,
  slot: 'titleCategoryField',
  overridesResolver(props, styles) {
    return [styles.titleCategoryField];
  }
})(({ theme }) => ({
  paddingBottom: theme.spacing(1)
}));

export default function SimpleCategory({
  config: { label, dataSource = [], defaultValue },
  name
}: FormFieldProps) {
  const classes = useStyles();
  const [field, , { setValue }] = useField(name);

  const menus = dataSource;

  const handleSelect = React.useCallback(
    (id: string) => {
      setValue(id);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  return (
    <div data-testid={camelCase(`field ${name}`)}>
      <BoxDivider>
        <Divider />
      </BoxDivider>
      <TitleCategoryField
        component="h4"
        variant="h4"
        color="textPrimary"
      >
        {label}
      </TitleCategoryField>
      <div>
        {menus.map(item => (
          <ItemView
            classes={classes}
            item={item}
            handleSelect={handleSelect}
            key={item.id}
            active={item.id === field.value}
          />
        ))}
      </div>
    </div>
  );
}

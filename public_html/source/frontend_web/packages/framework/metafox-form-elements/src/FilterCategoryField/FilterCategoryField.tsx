/**
 * @type: formElement
 * name: form.element.FilterCategory
 * chunkName: formElement
 */
import { useGlobal } from '@metafox/framework';
import { FormFieldProps } from '@metafox/form/types';
import { Box, Divider, Typography, styled } from '@mui/material';
import { useField } from 'formik';
import { camelCase, get } from 'lodash';
import * as React from 'react';
import ItemView from './ItemView';

const name = 'FilterCategoryField';

const DividerCategory = styled(Box, {
  name,
  slot: 'dividerCategory',
  overridesResolver(props, styles) {
    return [styles.dividerCategory];
  }
})(({ theme }) => ({
  paddingTop: theme.spacing(2),
  paddingBottom: theme.spacing(2)
 }));

 const TitleCategoryFilter = styled(Typography, {
  name,
  slot: 'titleCategoryFilter',
  overridesResolver(props, styles) {
    return [styles.titleCategoryFilter];
  }
 })(({ theme }) => ({
  paddingBottom: theme.spacing(1)
 }));

 const MenuCategoryFilter = styled(Box, {
  name,
  slot: 'menuCategoryFilter',
  overridesResolver(props, styles) {
    return [styles.menuCategoryFilter];
  }
 })(({ theme }) => ({
  paddingBottom: theme.spacing(3)
 }));

export default function FilterCategoryField({
  config: { label, apiUrl, dataSource = [], defaultValue },
  name
}: FormFieldProps) {
  const [, , { setValue }] = useField(name);
  const { useFetchItems, usePageParams, i18n } = useGlobal();
  const pageParams = usePageParams();
  const category_id = get(pageParams, name);

  const [items] = useFetchItems({
    dataSource: {
      apiUrl
    },
    data: [],
    cache: true
  });

  const menus = items.length > 0 ? items : dataSource;

  const handleSelect = React.useCallback(
    (id: string) => {
      setValue(id);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const handleReset = React.useCallback(() => {
    setValue(undefined);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <div data-testid={camelCase(`field ${name}`)}>
      <DividerCategory sx={{ pt: 2, pb: 2 }}>
        <Divider />
      </DividerCategory>
      <TitleCategoryFilter
        component="h4"
        variant="h4"
        color="textPrimary"
        sx={{ pb: 1 }}
      >
        {label}
      </TitleCategoryFilter>
      <MenuCategoryFilter>
        {menus && menus.length ? (
          <ItemView
            item={{ name: i18n.formatMessage({ id: 'all' }) }}
            handleSelect={handleReset}
            key={'all'}
            active={!category_id}
            category_id={category_id}
          />
        ) : null}
        {menus.map(item => (
          <ItemView
            item={item}
            handleSelect={handleSelect}
            key={item.id}
            active={category_id === String(item.id)}
            category_id={category_id}
          />
        ))}
      </MenuCategoryFilter>
    </div>
  );
}

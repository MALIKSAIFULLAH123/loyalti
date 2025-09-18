/**
 * @type: dialog
 * name: pages.dialog.PagesPicker
 */
import {
  SingleItemPickerDialogProps,
  useGlobal,
  useResourceAction,
  useSuggestions,
  RemoteDataSource
} from '@metafox/framework';
import { APP_PAGE } from '@metafox/pages';
import { SingleItemPickerDialog } from '@metafox/ui';
import React from 'react';

function PagesItemPicker(
  props: Omit<SingleItemPickerDialogProps, 'onChanged' | 'items'> & {
    value: any;
    dataSource: RemoteDataSource;
  }
) {
  const { i18n } = useGlobal();
  const { dataSource } = props || {};
  const [selectedItem, setSelectedItem] = React.useState(props.value);

  const config = useResourceAction(
    APP_PAGE,
    APP_PAGE,
    'getShareOnPageSuggestion'
  );

  const { apiUrl, apiParams } = Object.assign({}, dataSource || config);

  const [data, handleChanged] = useSuggestions({
    apiUrl,
    initialParams: apiParams
  });

  const onSelectItem = React.useCallback((item: any) => {
    setSelectedItem(item);
  }, []);

  return (
    <SingleItemPickerDialog
      placeholder={i18n.formatMessage({ id: 'search_page' })}
      dialogTitle={i18n.formatMessage({ id: 'select_page_to_share' })}
      items={data.items}
      loading={data.loading}
      onSelectItem={onSelectItem}
      selectedItem={selectedItem}
      onChanged={handleChanged}
      emptyPage="core.block.no_content"
      emptyPageProps={{ title: i18n.formatMessage({ id: 'no_pages_found' }) }}
      data-testid="popupPagePicker"
      gridLayout="Friend - Small List"
      itemLayout="Friend - Small List"
      itemView="page.ui.pickPageItem"
      {...props}
    />
  );
}

export default PagesItemPicker;

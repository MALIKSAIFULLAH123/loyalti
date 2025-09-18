/**
 * @type: dialog
 * name: group.dialog.GroupPicker
 */
import {
  SingleItemPickerDialogProps,
  useGlobal,
  useResourceAction,
  useSuggestions,
  RemoteDataSource
} from '@metafox/framework';
import { APP_GROUP } from '@metafox/group';
import { SingleItemPickerDialog } from '@metafox/ui';
import React from 'react';

function GroupPicker(
  props: Omit<SingleItemPickerDialogProps, 'onChange' | 'items'> & {
    value: any;
    dataSource: RemoteDataSource;
  }
) {
  const { i18n } = useGlobal();

  const { dataSource } = props || {};

  const config = useResourceAction(
    APP_GROUP,
    APP_GROUP,
    'getShareOnGroupSuggestion'
  );

  const { apiUrl, apiParams } = Object.assign({}, dataSource || config);

  const [data, handleChanged] = useSuggestions({
    apiUrl,
    initialParams: apiParams
  });

  const [selectedItem, setSelectedItem] = React.useState(props.value);

  const onSelectItem = React.useCallback((item: any) => {
    setSelectedItem(item);
  }, []);

  return (
    <SingleItemPickerDialog
      placeholder={i18n.formatMessage({ id: 'search_group' })}
      dialogTitle={i18n.formatMessage({ id: 'select_group_to_share' })}
      items={data.items}
      loading={data.loading}
      onSelectItem={onSelectItem}
      selectedItem={selectedItem}
      onChanged={handleChanged}
      data-testid="popupGroupPicker"
      emptyPage="core.block.no_content"
      emptyPageProps={{ title: i18n.formatMessage({ id: 'no_groups_found' }) }}
      gridLayout="Friend - Small List"
      itemLayout="Group - Item Picker"
      itemView="group.ui.pickGroupItem"
      {...props}
    />
  );
}

export default GroupPicker;

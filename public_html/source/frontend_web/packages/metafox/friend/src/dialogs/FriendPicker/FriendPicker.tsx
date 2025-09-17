/**
 * @type: dialog
 * name: friend.dialog.FriendPicker
 */
import {
  SingleItemPickerDialogProps,
  useGlobal,
  useSuggestions
} from '@metafox/framework';
import { SingleItemPickerDialog } from '@metafox/ui';
import React from 'react';

function FriendPicker(
  props: Omit<SingleItemPickerDialogProps, 'onChanged' | 'items'> & {
    value: any;
    apiUrl: string;
    initialParams: Record<string, any>;
  }
) {
  const { i18n } = useGlobal();

  const [data, handleChange] = useSuggestions({
    apiUrl: props.apiUrl || '/friend/mention',
    initialParams: props?.initialParams
  });

  const [selectedItem, setSelectedItem] = React.useState(props.value);

  const onSelectItem = React.useCallback((item: any) => {
    setSelectedItem(item);
  }, []);

  return (
    <SingleItemPickerDialog
      placeholder={i18n.formatMessage({ id: 'search_friend' })}
      dialogTitle={i18n.formatMessage({ id: 'select_friend' })}
      items={data.items}
      loading={data.loading}
      onSelectItem={onSelectItem}
      selectedItem={selectedItem}
      onChanged={handleChange}
      gridLayout="Friend - Small List"
      itemLayout="Friend - Small List"
      itemView="friend.ui.pickItem"
      emptyPage="core.block.no_content"
      emptyPageProps={{ title: i18n.formatMessage({ id: 'no_people_found' }) }}
      data-testid="popupFriendPicker"
      {...props}
    />
  );
}

export default FriendPicker;

/**
 * @type: dialog
 * name: friend.dialog.MultipleFriendPicker
 */
import {
  MultipleItemPickerDialogProps,
  useGlobal,
  useSuggestions
} from '@metafox/framework';
import { MultipleItemPickerDialog } from '@metafox/ui';
import { concat, some } from 'lodash';
import React, { useState } from 'react';

function MultipleFriendPicker(
  props: Omit<MultipleItemPickerDialogProps, 'onChange' | 'items'> & {
    value: any;
    apiUrl: string;
    initialParams: Record<string, any>;
  }
) {
  const { i18n, useTheme } = useGlobal();
  const theme = useTheme();

  const [data, handleChange] = useSuggestions({
    apiUrl: props.apiUrl || '/friend/mention',
    initialParams: props?.initialParams
  });

  const [selectedItems, setSelectedItems] = useState(props.value || []);

  const handleSelectItem = item => {
    setSelectedItems(prev => {
      return some(prev, x => x.id === item.id)
        ? prev.filter(x => x.id !== item.id)
        : concat(prev, item);
    });
  };

  return (
    <MultipleItemPickerDialog
      items={data.items}
      onSelectItem={handleSelectItem}
      selectedItems={selectedItems}
      onChanged={handleChange}
      loading={data.loading}
      placeholder={i18n.formatMessage({ id: 'search_for_friends' })}
      dialogTitle={i18n.formatMessage({ id: 'tag_people' })}
      emptyPage="core.block.no_content"
      emptyPageProps={{ title: i18n.formatMessage({ id: 'no_people_found' }) }}
      gridLayout="Friend - Small List"
      itemLayout="Friend - Small List"
      itemView="friend.ui.pickItem"
      heightContent={theme.spacing(37.5)}
      {...props}
    />
  );
}

export default MultipleFriendPicker;

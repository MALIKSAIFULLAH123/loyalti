import { useGlobal } from '@metafox/framework';
import { DialogContent, DialogTitle } from '@metafox/dialog';
import { ScrollContainer } from '@metafox/layout';
import { DialogSearchInput, LineIcon } from '@metafox/ui';
import { escapeRegExp } from '@metafox/utils';
import {
  Box,
  Dialog,
  IconButton,
  List,
  ListItem,
  ListItemText
} from '@mui/material';
import { map } from 'lodash';
import React from 'react';

interface Props {
  allItems: string[];
  title: string;
  onEdit: (name: string) => void;
}
export default function ManageItemDialog({ allItems, title, onEdit }: Props) {
  const { useDialog, i18n } = useGlobal();
  const { dialogProps } = useDialog();
  const [items, setItems] = React.useState<string[]>(allItems);
  const [query, setQuery] = React.useState<string>('');

  const handleEdit = (name: string) => {
    if (!allItems.includes(name)) {
      setItems(prev => [name, ...prev]);
    }

    onEdit(name);
  };

  React.useEffect(() => {
    if (!query) {
      setItems(allItems);
    } else {
      const reg = new RegExp(escapeRegExp(query), 'i');
      setItems(allItems.filter(x => reg.test(x)));
    }
  }, [allItems, query]);

  return (
    <Dialog maxWidth="xs" fullWidth {...dialogProps}>
      <DialogTitle>{title}</DialogTitle>
      <DialogContent variant="fit">
        <Box sx={{ m: 2 }}>
          <DialogSearchInput
            autoFocus
            onChanged={setQuery}
            placeholder={i18n.formatMessage({ id: 'search_dots' })}
          />
        </Box>
        <ScrollContainer
          autoHeightMax={200}
          autoHeight={false}
          style={{ height: 200 }}
        >
          <List>
            {map(items, name => (
              <ListItem key={name.toString()}>
                <ListItemText primary={name} />
                <IconButton size="smaller" onClick={() => handleEdit(name)}>
                  <LineIcon icon="ico-pencil" />
                </IconButton>
              </ListItem>
            ))}
            {!items.length && query ? (
              <ListItem>
                <ListItemText primary={`Add new "${query}"`} />
                <IconButton size="smaller" onClick={() => handleEdit(query)}>
                  <LineIcon icon="ico-plus" />
                </IconButton>
              </ListItem>
            ) : null}
          </List>
        </ScrollContainer>
      </DialogContent>
    </Dialog>
  );
}

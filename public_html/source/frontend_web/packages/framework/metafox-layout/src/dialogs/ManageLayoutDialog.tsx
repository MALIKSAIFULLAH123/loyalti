/**
 * @type: dialog
 * name: layout.dialog.ManageLayouts
 * chunkName: layoutEditor
 */
import { DialogContent, DialogTitle } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
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

type Item = {
  value: string;
  label: string;
  description: string;
  thumbnail: string;
};

function ManageLayoutDialog() {
  const { useDialog, layoutBackend, dialogBackend, i18n } = useGlobal();
  const { dialogProps } = useDialog();
  const [query, setQuery] = React.useState<string>('');
  const title = i18n.formatMessage({ id: 'manage_layout_templates' });
  const [rev, setRev] = React.useState(0);
  const templates = layoutBackend.getTemplates();

  const refresh = () => setRev(rev => rev + 1);

  const handleEdit = React.useCallback((templateName: string) => {
    dialogBackend
      .present({
        component: 'layout.dialog.EditTemplateInfo',
        props: { templateName }
      })
      .then(values => {
        templates[templateName] = { ...templates[templateName], ...values };
      })
      .finally(refresh);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const items: Item[] = React.useMemo(() => {
    const arr = Object.keys(templates).map(name => {
      const item = templates[name];

      return {
        value: name,
        label: item.title ?? name,
        description: item.title,
        thumbnail: item.thumbnail
      };
    });

    if (!query) return arr;

    const reg = new RegExp(escapeRegExp(query), 'i');

    return arr.filter(x => reg.test(x.value) || reg.test(x.label));

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rev, query]);

  return (
    <Dialog {...dialogProps} maxWidth="xs">
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
            {map(items, item => (
              <ListItem key={item.value}>
                <ListItemText primary={item.label} />
                <IconButton
                  size="smaller"
                  onClick={() => handleEdit(item.value)}
                >
                  <LineIcon icon="ico-pencil" />
                </IconButton>
              </ListItem>
            ))}
          </List>
        </ScrollContainer>
      </DialogContent>
    </Dialog>
  );
}

export default ManageLayoutDialog;

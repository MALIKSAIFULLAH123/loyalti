/**
 * @type: dialog
 * name: layout.dialog.ChooseLayout
 * chunkName: layoutEditor
 */
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { SearchBox } from '@metafox/ui';
import { escapeRegExp } from '@metafox/utils';
import CreateIcon from '@mui/icons-material/AddBoxOutlined';
import { Box, styled, Tooltip, Typography } from '@mui/material';
import React from 'react';

const Item = styled(Box, {
  name: 'ChooseTemplate',
  slot: 'Item'
})<{ selected?: boolean }>(({ theme, selected }) => ({
  cursor: 'pointer',
  padding: 8,
  transformOrigin: 'bottom center',
  transitionDuration: '200ms',
  ...(selected && {
    transform: 'scale(1.1)',
    '&>div': {
      border: `2px solid ${theme.palette.primary.main}`
    }
  }),
  '&:hover': {
    transform: 'scale(1.1)'
  }
}));

const Img = styled(Box, {
  name: 'ChooseTemplate',
  slot: 'Image',
  shouldForwardProp: (prop: string) => !/image/i.test(prop)
})<{ image: string }>(({ theme, image }) => ({
  width: 80,
  height: 80,
  backgroundRepeat: 'no-repeat',
  backgroundSize: '80px 80px',
  backgroundPosition: 'center',
  backgroundImage: `url(${image})`
}));

export default function ChooseLayoutDialog({
  pageName,
  templateName,
  pageSize
}) {
  const { useDialog, dispatch, i18n, layoutBackend } = useGlobal();
  const { setDialogValue, closeDialog, dialogProps } = useDialog();
  const [created, setCreated] = React.useState<boolean>(false);
  const templates = layoutBackend.getTemplates();

  const onSelect = React.useCallback(
    (value: string) => {
      if (!created) {
        setDialogValue(value);
      } else {
        closeDialog();
        dispatch({
          type: '@layout/createTemplate',
          payload: { baseTemplate: value, pageName, pageSize }
        });
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [created]
  );

  const toggleCreate = React.useCallback(() => setCreated(value => !value), []);

  const allItems = React.useMemo(() => {
    return Object.keys(templates)
      .map((name: string) => {
        const item = templates[name];

        return {
          value: name,
          label: name,
          description: name,
          thumbnail: item.thumbnail
        };
      })
      .filter(x => !['site.small', 'site.large'].includes(x.value));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const [query, setQuery] = React.useState<string>();

  const items = React.useMemo(() => {
    if (!query) return allItems;

    const reg = new RegExp(escapeRegExp(query), 'i');

    return allItems.filter(x => reg.test(x.value));
  }, [allItems, query]);

  return (
    <Dialog maxWidth="sm" fullWidth {...dialogProps}>
      <DialogTitle enableBack={created} onBackClick={toggleCreate}>
        {i18n.formatMessage({
          id: created ? 'create_new_layout' : 'choose_layout'
        })}
      </DialogTitle>
      <DialogContent variant="fix" sx={{ px: 1, py: 1 }}>
        <Box sx={{ p: 2, display: 'none' }}>
          <SearchBox
            autoFocus
            onChange={evt => setQuery(evt.target.value)}
            placeholder={i18n.formatMessage({ id: 'search_dots' })}
          />
        </Box>
        {created ? (
          <Box px={1}>
            <Typography color="text.secondary" paragraph>
              Choose a layout then continue
            </Typography>
          </Box>
        ) : null}
        <Box sx={{ display: 'flex', flexWrap: 'wrap' }}>
          {items.map(item => (
            <Item key={item.value} selected={item.value === templateName}>
              <Tooltip title={item.label}>
                <Img
                  onClick={() => onSelect(item.value)}
                  image={
                    item.thumbnail ??
                    `https://metafox-dev.s3.amazonaws.com/kl/layouts/${item.value}.jpeg`
                  }
                />
              </Tooltip>
            </Item>
          ))}
          {created ? null : (
            <Item key=".create">
              <Tooltip title={i18n.formatMessage({ id: 'create_new_layout' })}>
                <Box onClick={toggleCreate}>
                  <CreateIcon
                    sx={{ width: 80, height: 80, color: 'primary.main' }}
                  />
                </Box>
              </Tooltip>
            </Item>
          )}
        </Box>
      </DialogContent>
    </Dialog>
  );
}

/**
 * @type: dialog
 * name: layout.dialog.ManageHistories
 * chunkName: layoutEditor
 */
import { DialogContent, DialogTitle } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { FromNow, LineIcon } from '@metafox/ui';
import {
  Box,
  Button,
  Dialog,
  IconButton,
  List,
  ListItem,
  ListItemButton,
  ListItemText,
  styled,
  Tooltip
} from '@mui/material';
import { map } from 'lodash';
import React from 'react';

const LoadingStyled = styled(Box)(({ theme }) => ({
  marginTop: theme.spacing(3)
}));

// import ClearAllIcon from '@mui/icons-material/ClearAll';

export type Item = {
  value: string;
  label: string;
  description: string;
  thumbnail: string;
};

function Histories({ snippet, clearLabel }) {
  const { useFetchItems, dispatch, i18n, jsxBackend } = useGlobal();
  const [selected, setSelected] = React.useState();

  const revert = React.useCallback((id: number) => {
    dispatch({ type: '@layout/revert', payload: id });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const discard = React.useCallback(() => {
    dispatch({ type: '@layout/clearHistory', payload: snippet });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [snippet]);

  const [items, loading] = useFetchItems({
    dataSource: {
      apiUrl: '/layout/snippet/history/:snippet'
    },
    pageParams: { snippet }
  });

  const handleClick = id => {
    setSelected(id);
    revert(id);
  };

  if (loading) {
    return (
      <LoadingStyled>
        {jsxBackend.render({ component: 'form.DefaultLoading' })}
      </LoadingStyled>
    );
  }

  return (
    <List>
      {map(items, item => (
        <ListItem
          key={item.id}
          selected={selected ? selected === item.id : item.active}
        >
          <ListItemText
            primary={item.name}
            secondary={<FromNow value={item.created_at} />}
          />
          <IconButton
            size="smaller"
            disabled={selected ? selected === item.id : item.active}
            onClick={() => handleClick(item.id)}
          >
            <Tooltip title={i18n.formatMessage({ id: 'layout_revert' })}>
              <LineIcon icon="ico-undo" />
            </Tooltip>
          </IconButton>
        </ListItem>
      ))}
      {!items.length && !loading ? (
        <ListItem>
          <ListItemText
            primary={i18n.formatMessage({ id: 'layout_no_histories' })}
          />
        </ListItem>
      ) : null}
      {items.length ? (
        <ListItem disableGutters>
          <Button color="error" onClick={discard}>
            {clearLabel}
          </Button>
        </ListItem>
      ) : null}
    </List>
  );
}

function ManageHistories({ theme, variant }) {
  const { useDialog, i18n } = useGlobal();
  const { dialogProps } = useDialog();
  const title = i18n.formatMessage({ id: 'layout_view_histories' });
  const [type, setType] = React.useState<string>('layout');

  return (
    <Dialog {...dialogProps} maxWidth="sm" hideBackdrop>
      <DialogTitle>{title}</DialogTitle>
      <DialogContent sx={{ padding: 0 }}>
        <Box sx={{ display: 'flex', flexDirection: 'row' }}>
          <Box
            sx={{ width: 220, mr: 2, borderRight: '1px solid rgba(0,0,0,0.1)' }}
          >
            <ListItemButton
              disableRipple
              onClick={() => setType('layout')}
              selected={type === 'layout'}
            >
              <ListItemText
                primary={i18n.formatMessage({ id: 'layout_and_presets' })}
              />
            </ListItemButton>
            <ListItemButton
              disableRipple
              onClick={() => setType('variant')}
              selected={type === 'variant'}
            >
              <ListItemText primary={i18n.formatMessage({ id: 'styling' })} />
            </ListItemButton>
          </Box>
          <Box sx={{ flex: 1 }}>
            <ScrollContainer
              autoHeightMax={360}
              style={{ height: 360 }}
              autoHeight={false}
            >
              {type === 'layout' ? (
                <Histories
                  key="layout"
                  snippet={theme}
                  clearLabel={i18n.formatMessage({
                    id: 'remove_all_layout_and_presets_histories'
                  })}
                />
              ) : null}
              {type === 'variant' ? (
                <Histories
                  key="variant"
                  snippet={variant}
                  clearLabel={i18n.formatMessage({
                    id: 'remove_styling_histories'
                  })}
                />
              ) : null}
            </ScrollContainer>
          </Box>
        </Box>
      </DialogContent>
    </Dialog>
  );
}

export default ManageHistories;

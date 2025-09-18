/**
 * @type: dialog
 * name: saved.dialog.saveToCollection
 */

import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { Box, Button, DialogActions, styled, Typography } from '@mui/material';
import { isEqual } from 'lodash';
import React from 'react';
import AddCollectionName from './AddCollectionName';

const ButtonIcon = styled(Button)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  minWidth: theme.spacing(6.25),
  minHeight: theme.spacing(6),
  '& p': {
    fontWeight: theme.typography.fontWeightMedium
  },
  '& .ico': {
    marginRight: theme.spacing(1),
    fontSize: theme.mixins.pxToRem(15),
    fontWeight: theme.typography.fontWeightMedium
  }
}));

const DialogContentStyled = styled(DialogContent)(({ theme }) => ({
  padding: theme.spacing(1),
  paddingBottom: 0,
  display: 'flex',
  flexDirection: 'column'
}));

const WrapperContent = styled(Box)(({ theme }) => ({
  minHeight: 0,
  height: '300px',
  [theme.breakpoints.down('sm')]: {
    height: 'unset',
    flex: 1
  }
}));

interface Props {
  item: any;
}

const ListCollections = ({
  item,
  collection_ids,
  handleToggleItem,
  checkedList,
  submitting
}: any) => {
  const { jsxBackend, useIsMobile, BatchSelectProvider } = useGlobal();
  const isMobile = useIsMobile();

  const SavedListCollection = jsxBackend.get('saved.block.savedListAddBlock');

  const context = {
    item,
    collection_ids,
    handleToggleItem,
    loading: submitting,
    checkedList
  };

  const EmptyPage = jsxBackend.get('saved.ui.noCollectionList');

  if (!SavedListCollection) return React.createElement(EmptyPage);

  return (
    <ScrollContainer
      autoHide
      autoHeight
      autoHeightMax={isMobile ? '100%' : 300}
    >
      <BatchSelectProvider value={context}>
        <SavedListCollection />
      </BatchSelectProvider>
    </ScrollContainer>
  );
};

export default function SaveToCollectionDetail({ item }: Props) {
  const { useDialog, i18n, dispatch, getAcl } = useGlobal();
  const hasCreateSaveList = getAcl('saved.saved_list.create');
  const dialogItem = useDialog();
  const { dialogProps, closeDialog } = dialogItem;
  const [isToggleName, setIsToggleName] = React.useState(false);

  const identity = item._identity;
  const { collection_ids = [] } = item || {};

  const [checkedList, setCheckedList] = React.useState(collection_ids || []);
  const [submitting, setSubmitting] = React.useState<boolean>(false);

  const handleToggleName = () => {
    setIsToggleName(prev => !prev);
  };

  const handleToggleItem = (idItem, isChecked) => {
    const data = isChecked
      ? [...checkedList, idItem]
      : checkedList.filter(x => x !== idItem);

    setCheckedList(data);
  };

  const handleSubmit = () => {
    if (isEqual(collection_ids, checkedList)) return;

    setSubmitting(true);

    dispatch({
      type: 'addSavedItemToCollection',
      payload: {
        identity,
        ids: checkedList,
        initCollections: collection_ids
      },
      meta: {
        onSuccess: () => {
          setSubmitting(false);
          closeDialog();
        }
      }
    });
  };

  return (
    <Dialog
      data-testid="saved.dialog.saveToCollection"
      {...dialogProps}
      maxWidth="xs"
    >
      <DialogTitle
        children={i18n.formatMessage({ id: 'add_to_collections' })}
        data-testid="popupTitle"
      />
      <DialogContentStyled>
        <WrapperContent>
          <ListCollections
            item={item}
            collection_ids={collection_ids}
            handleToggleItem={handleToggleItem}
            checkedList={checkedList}
            submitting={submitting}
          />
        </WrapperContent>
        {hasCreateSaveList ? (
          <Box mt={1}>
            {isToggleName ? (
              <AddCollectionName handleToggleName={handleToggleName} />
            ) : (
              <ButtonIcon
                variant="text"
                color="primary"
                onClick={handleToggleName}
                data-testid="new_collection"
              >
                <LineIcon icon="ico-plus" />
                <Typography variant="body1">
                  {i18n.formatMessage({ id: 'new_collection' })}
                </Typography>
              </ButtonIcon>
            )}
          </Box>
        ) : null}
      </DialogContentStyled>
      <DialogActions>
        <Button
          variant="contained"
          onClick={handleSubmit}
          size="small"
          color="primary"
          data-testid="buttonSubmit"
          disabled={isEqual(collection_ids, checkedList) || submitting}
        >
          {i18n.formatMessage({ id: 'save' })}
        </Button>
      </DialogActions>
    </Dialog>
  );
}

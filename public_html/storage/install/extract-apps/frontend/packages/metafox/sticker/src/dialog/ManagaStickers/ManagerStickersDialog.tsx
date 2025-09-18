/**
 * @type: dialog
 * name: dialog.sticker.manager
 */

import { Dialog, DialogTitle, DialogContent } from '@metafox/dialog';
import { Tab, Tabs, styled, Typography, Button, Box } from '@mui/material';
import React, { useState } from 'react';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { ALL_STICKERS, MY_STICKERS } from '@metafox/sticker/constant';
import { LineIcon } from '@metafox/ui';

const TabStyled = styled(Tab, { name: 'TabStyled' })(({ theme }) => ({
  padding: '0!important',
  fontSize: theme.mixins.pxToRem(15),
  fontWeight: 'bold',
  marginRight: theme.spacing(3.5)
}));

const TabsStyled = styled(Tabs, { name: 'TabsStyled' })(({ theme }) => ({
  '& div>span': {
    height: theme.spacing(0.5)
  }
}));

const DialogContentStyled = styled(DialogContent, {
  name: 'DialogContentStyled'
})(({ theme }) => ({
  padding: `${theme.spacing(0, 1, 0, 2)}!important`,
  height: '50vh'
}));

const EmptySticker = styled(Box, { name: 'EmptySticker' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  color: theme.palette.text.secondary,
  alignItems: 'center',
  justifyContent: 'center',
  height: '100%'
}));

const IconEmpty = styled(LineIcon, { name: 'iconEmpty' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(40),
  marginBottom: theme.spacing(1)
}));

export default function ManagerStickerDialog(props) {
  const { itemView } = props;

  const { useDialog, i18n, jsxBackend } = useGlobal();
  const scrollRef = React.useRef<HTMLDivElement>();

  const { dialogProps } = useDialog();
  const defaultTab = MY_STICKERS;
  const [value, setValue] = useState<string>(defaultTab);

  const ListView = jsxBackend.get('core.block.listview');

  const onChangeTab = (event: React.ChangeEvent<{}>, newValue: string) => {
    scrollRef.current && scrollRef.current.scrollTo({ top: 0 });
    setValue(newValue);
  };

  const openAllStickerSet = () => {
    setValue(ALL_STICKERS);
  };

  const emptyPage = (
    <EmptySticker>
      <IconEmpty icon="ico-sticker" />
      <Typography mb={2} fontSize={20}>
        {i18n.formatMessage({
          id:
            value === MY_STICKERS
              ? 'no_stickers_are_found_add_some_stickers_to_your_library_now'
              : 'no_sticker_found'
        })}
      </Typography>
      {value === MY_STICKERS && (
        <Button variant="contained" size="medium" onClick={openAllStickerSet}>
          {i18n.formatMessage({ id: 'add' })}
        </Button>
      )}
    </EmptySticker>
  );

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <DialogTitle>
        {i18n.formatMessage({ id: 'manager_stickers' })}
      </DialogTitle>
      <DialogContentStyled variant="fitScroll">
        <TabsStyled value={value} onChange={onChangeTab}>
          <TabStyled
            value={MY_STICKERS}
            label={i18n.formatMessage({ id: 'my_stickers' })}
          />
          <TabStyled
            value={ALL_STICKERS}
            label={i18n.formatMessage({ id: 'all_stickers' })}
          />
        </TabsStyled>

        <ScrollContainer
          ref={scrollRef}
          autoHeightMax={'100%'}
          autoHide
          autoHeight
        >
          <ListView
            moduleName="sticker"
            resourceName="sticker_set"
            actionName={value === ALL_STICKERS ? 'viewAll' : 'viewMyStickerSet'}
            emptyPage={emptyPage}
            errorPage="hide"
            itemView={itemView}
            canLoadMore
            canLoadSmooth
            numberOfItemsPerPage={8}
            cachedEmpty={false}
          />
        </ScrollContainer>
      </DialogContentStyled>
    </Dialog>
  );
}

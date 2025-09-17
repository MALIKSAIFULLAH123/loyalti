import { useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import { Box, Dialog, styled } from '@mui/material';
import React from 'react';
import ItemStat from './ItemStat';

const Wrapper = styled(Box, { slot: 'Box' })(({ theme }) => ({
  overflow: 'auto'
}));

const DialogWrapper = styled(Dialog, { slot: 'DialogWrapper' })(
  ({ theme }) => ({
    '& .MuiBox-root': {
      overflow: 'auto',
      margin: 0
    }
  })
);
type SimpleDialogProps = {
  onClose: () => void;
  data: Array<Record<string, any>>;
  title: string;
  tabId?: number;
};
export default function SimpleDialog(props: SimpleDialogProps) {
  const { onClose, data, title, tabId: tabIdDefault } = props;
  const { i18n } = useGlobal();
  const [tabId, setTabId] = React.useState<number>(tabIdDefault || 0);

  const changeTab = (_, value) => {
    setTabId(value);
  };

  const handleClose = () => {
    onClose();
  };

  return (
    <DialogWrapper fullWidth="true" maxWidth="xs" onClose={handleClose} open>
      <Block>
        <Wrapper>
          <BlockHeader>
            <BlockTitle>{i18n.formatMessage({ id: title })}</BlockTitle>
          </BlockHeader>
          <BlockContent>
            {data.map((x, index) => (
              <ItemStat
                key={`k${index}`}
                item={x}
                tabId={tabId}
                changeTab={changeTab}
              />
            ))}
          </BlockContent>
        </Wrapper>
      </Block>
    </DialogWrapper>
  );
}

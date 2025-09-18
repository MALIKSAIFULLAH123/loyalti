import { useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockDesc } from '@metafox/layout';
import { Card, ListItem, Switch, Typography, styled } from '@mui/material';
import React, { useEffect, useState } from 'react';
import { useDispatch } from 'react-redux';

const CardItem = styled(Card, {
  name: 'LayoutSlot',
  slot: 'rootBorder',
  overridesResolver(props, styles) {
    return [styles.rootBorder];
  }
 })(({ theme }) => ({
  boxShadow: 'none'
 }));

const PendingMode = ({ title, identity, item, ...rest }: any) => {
  const { i18n } = useGlobal();
  const dispatch = useDispatch();

  const [value, setValue] = useState(!!item?.pending_mode);
  const [loadingSubmit, setLoadingSubmit] = useState(false);

  useEffect(() => {
    if (!item) return;

    setValue(!!item?.pending_mode);
  }, [item]);

  const handleChange = (checked: boolean) => {
    setLoadingSubmit(true);
    setValue(checked);

    dispatch({
      type: 'group/updatePendingMode',
      payload: { identity, pending_mode: checked },
      meta: {
        onSuccess: () => setLoadingSubmit(false),
        onFailure: () => setLoadingSubmit(false)
      }
    });
  };

  return (
    <Block {...rest}>
      <BlockHeader title={title} />
      <BlockDesc variant="body2" paragraph>
        {i18n.formatMessage({ id: 'pending_mode_description' })}
      </BlockDesc>
      <BlockContent>
        <CardItem>
          <ListItem
            sx={{ py: 4 }}
            secondaryAction={
              <Switch
                onChange={(_, checked) => handleChange(checked)}
                checked={value}
                size="medium"
                color="primary"
                disabled={loadingSubmit}
              />
            }
          >
            <Typography component="div" variant="body1">
              {i18n.formatMessage({ id: 'enable_pending_mode' })}
            </Typography>
          </ListItem>
        </CardItem>
      </BlockContent>
    </Block>
  );
};

export default PendingMode;

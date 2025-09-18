import { useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockDesc, BlockHeader } from '@metafox/layout';
import { Card, ListItem, Switch, Typography, styled } from '@mui/material';
import React, { useState } from 'react';
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

const RuleMode = ({ title, identity, item, ...rest }: any) => {
  const { i18n } = useGlobal();
  const dispatch = useDispatch();

  const [value, setValue] = useState(!!item?.is_answer_membership_question);
  const [loadingSubmit, setLoadingSubmit] = useState(false);

  const handleChange = (checked: boolean) => {
    setLoadingSubmit(true);
    setValue(checked);

    dispatch({
      type: 'group/updateMembershipQuestionConfirmation',
      payload: { identity, is_answer_membership_question: checked },
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
        {i18n.formatMessage({ id: 'answer_membership_mode_description' })}
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
              {i18n.formatMessage({ id: 'enable_answer_membership_mode' })}
            </Typography>
          </ListItem>
        </CardItem>
      </BlockContent>
    </Block>
  );
};

export default RuleMode;

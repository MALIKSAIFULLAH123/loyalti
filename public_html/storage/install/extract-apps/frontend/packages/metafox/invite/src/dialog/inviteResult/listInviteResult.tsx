/**
 * @type: dialog
 * name: invite.dialog.listInviteResult
 */

import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { UserAvatar, UserName } from '@metafox/ui';
import { TabContext, TabPanel } from '@mui/lab';
import { Tab, Tabs, styled, Typography, Box } from '@mui/material';
import { isEmpty } from 'lodash';
import React, { useState } from 'react';

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

const ItemUser = styled(Box, { name: 'itemUser' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(2, 0),
  borderBottom: '1px solid',
  borderBottomColor: theme.palette.divider
}));

export default function ListInviteResult({
  communities,
  duplicates
}: {
  communities?: any[];
  duplicates?: any[];
}) {
  const { i18n, useDialog } = useGlobal();
  const { dialogProps } = useDialog();
  const [value, setValue] = useState<string>('1');

  const onChangeTab = (event: React.ChangeEvent<{}>, newValue: string) => {
    setValue(newValue);
  };

  return (
    <Dialog {...dialogProps}>
      <TabContext value={value}>
        <DialogTitle>
          {i18n.formatMessage({ id: 'invitation_result' })}
        </DialogTitle>
        <DialogContent sx={{ pt: 0 }} style={{ minHeight: '45vh' }}>
          <TabsStyled value={value} onChange={onChangeTab}>
            <TabStyled
              value="1"
              label={i18n.formatMessage({ id: 'invitation_before' })}
            />
            <TabStyled
              value="2"
              label={i18n.formatMessage({ id: 'registered_users' })}
            />
          </TabsStyled>
          <TabPanel value="1" sx={{ py: 2, px: 0 }}>
            <Typography variant="h5">
              {i18n.formatMessage({
                id: 'the_following_emails_or_phone_numbers_were_not_sent'
              })}
            </Typography>
            <Box>
              {isEmpty(duplicates) ? (
                <Box mt={2}>
                  {i18n.formatMessage({ id: 'no_invites_found' })}
                </Box>
              ) : (
                duplicates.map((user, index) => (
                  <ItemUser key={index.toString()}>{user}</ItemUser>
                ))
              )}
            </Box>
          </TabPanel>
          <TabPanel value="2" sx={{ py: 2, px: 0 }}>
            <Typography variant="h5">
              {i18n.formatMessage({
                id: 'the_following_users_are_already_a_member_of_our_community'
              })}
            </Typography>
            <Box>
              {isEmpty(communities) ? (
                <Box mt={2}>
                  {i18n.formatMessage({ id: 'no_members_found' })}
                </Box>
              ) : (
                communities.map((user, index) => (
                  <ItemUser key={index.toString()}>
                    <UserAvatar user={user} size={48} hoverCard={false} />
                    <UserName
                      ml={1.5}
                      user={user}
                      color={'inherit'}
                      hoverCard={false}
                      variant="h5"
                    />
                  </ItemUser>
                ))
              )}
            </Box>
          </TabPanel>
        </DialogContent>
      </TabContext>
    </Dialog>
  );
}

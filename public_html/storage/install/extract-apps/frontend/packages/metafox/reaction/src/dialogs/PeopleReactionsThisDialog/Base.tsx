import { Dialog, DialogTitle } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { ScrollProvider } from '@metafox/layout';
import { PeopleWhoReactionThisProps } from '@metafox/reaction/types';
import {
  Button,
  DialogActions,
  DialogContent,
  Skeleton,
  Tab,
  Tabs,
  Typography
} from '@mui/material';
import React, { useEffect, useMemo, useRef, useState } from 'react';
import useStyles from './styles';

const tabsStyle = {
  minHeight: 32
};

const tabStyle = {
  minWidth: 16
};

const iconStyle = {
  width: 15,
  height: 15
};

const TabSkeleton = () => {
  return (
    <>
      <Skeleton
        variant="button"
        width={40}
        height={24}
        sx={{ marginRight: 1 }}
      />
      <Skeleton
        variant="button"
        width={60}
        height={24}
        sx={{ marginRight: 1 }}
      />
      <Skeleton
        variant="button"
        width={60}
        height={24}
        sx={{ marginRight: 1 }}
      />
    </>
  );
};

export default function PeopleWhoReactionThis(
  props: PeopleWhoReactionThisProps
) {
  const { item_type, item_id, reactId, identity: identityParent } = props;
  const classes = useStyles();
  const [value, setValue] = useState<number>(reactId);
  const scrollRef = useRef();
  const { useDialog, i18n, useFetchItems, dispatch, navigate, jsxBackend } =
    useGlobal();
  const { dialogProps, closeDialog } = useDialog();

  const ListViewReaction = jsxBackend.get('reaction.block.PeopleReactions');

  const [tabs, loading, error] = useFetchItems({
    dataSource: {
      apiUrl: '/preaction/reaction-tabs',
      apiParams: { item_type, item_id }
    },
    data: []
  });

  useEffect(() => {
    if (tabs?.length) {
      dispatch({
        type: 'updateResourceReaction',
        payload: { tabResourceReaction: tabs }
      });
    }

    if (!reactId && tabs?.length) {
      setValue(tabs[0].id);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [tabs, reactId]);

  const dataSource = useMemo(
    () => ({
      apiUrl: '/preaction/get-reacted-lists',
      apiParams: {
        item_type,
        item_id,
        react_id: value,
        limit: 20
      }
    }),
    [item_id, item_type, value]
  );

  const handleRefresh = () => {
    navigate('/');
    closeDialog();
  };

  if (error) {
    return (
      <Dialog {...dialogProps} maxWidth="xs" fullWidth>
        <DialogTitle>Error</DialogTitle>
        <DialogContent dividers={false}>
          <Typography sx={{ pt: 2 }} variant="body1">
            {i18n.formatMessage({ id: 'error_post_delete' })}
          </Typography>
        </DialogContent>
        <DialogActions>
          <Button
            data-testid="buttonBackHome"
            role="button"
            tabIndex={1}
            autoFocus
            variant="contained"
            disableRipple
            size="medium"
            color="primary"
            onClick={handleRefresh}
            sx={{ minWidth: 100 }}
          >
            {i18n.formatMessage({ id: 'refresh' })}
          </Button>
        </DialogActions>
      </Dialog>
    );
  }

  const pagingId = `preaction/${item_type}/${item_id}/${value}`;

  const handleChange = (_event: React.ChangeEvent<{}>, newValue: string) => {
    setValue(parseInt(newValue, 10));
  };

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <DialogTitle>{i18n.formatMessage({ id: 'reacted_by' })}</DialogTitle>
      <Tabs
        value={value}
        onChange={handleChange}
        style={tabsStyle}
        className={classes.customTabs}
      >
        {!loading ? (
          tabs.map(tab => (
            <Tab
              key={tab.title}
              style={tabStyle}
              disableRipple
              icon={
                tab.icon ? (
                  <img src={tab.icon} alt={tab.title} style={iconStyle} />
                ) : (
                  <span className={classes.reactNumber}>{tab.title}</span>
                )
              }
              iconPosition="start"
              label={
                tab.total_reacted ? (
                  <span className={classes.reactNumber}>
                    {tab.total_reacted}
                  </span>
                ) : undefined
              }
              value={tab.id}
              aria-label={tab.title}
            />
          ))
        ) : (
          <TabSkeleton />
        )}
      </Tabs>
      <div className={classes.dialogContent} ref={scrollRef}>
        <ScrollProvider scrollRef={scrollRef}>
          {!ListViewReaction && loading ? null : (
            <ListViewReaction
              dataSource={dataSource}
              pagingId={pagingId}
              canLoadMore
              clearDataOnUnMount
              itemProps={{ identityParent }}
            />
          )}
        </ScrollProvider>
      </div>
    </Dialog>
  );
}

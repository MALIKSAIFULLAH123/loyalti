/**
 * @type: service
 * name: TotalComment
 */
import { useGlobal } from '@metafox/framework';
import { Box, styled, Tooltip } from '@mui/material';
import React from 'react';

const Wrapper = styled('div', { name: 'TotalComment' })(({ theme }) => ({
  marginLeft: theme.spacing(2),
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.text.secondary,
  [theme.breakpoints.up('sm')]: {
    '&:hover': {
      textDecoration: 'underline'
    }
  }
}));

const ListUser = ({
  itemIdentity,
  reloadListUser,
  setReloadListUser,
  dataSource
}) => {
  const { dispatch, i18n, useGetItem } = useGlobal();

  const [itemList, setItemList] = React.useState([]);
  const [leftUser, setLeftUser] = React.useState(0);
  const [loading, setLoading] = React.useState(false);
  const item = useGetItem(itemIdentity);

  const {
    item_type,
    item_id,
    resource_name,
    id,
    comment_item_id,
    comment_type_id
  } = item || {};

  React.useLayoutEffect(() => {
    let mounted = true;
    setLoading(true);
    dispatch({
      type: 'getCommentListUser',
      payload: {
        id,
        item_id: comment_item_id || item_id || id,
        item_type: comment_type_id || item_type || resource_name,
        resource_name,
        limit: 10,
        itemIdentity,
        forceReload: reloadListUser,
        dataSource
      },
      meta: {
        onSuccess: ({ data, meta }) => {
          if (!mounted) return;

          setLoading(false);
          setItemList(data);
          setLeftUser(Math.max(meta?.total - data.length, 0));
          setReloadListUser(false);
        }
      }
    });

    return () => {
      mounted = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dispatch, itemIdentity]);

  if (loading) return <Box>{i18n.formatMessage({ id: 'loading_dots' })}</Box>;

  return (
    <Box>
      {!!itemList.length &&
        itemList.map((item, index) => (
          <div key={index.toString()}>{item?.full_name}</div>
        ))}
      {!!leftUser && (
        <div>
          {i18n.formatMessage(
            { id: 'and_value_more_dots' },
            { value: leftUser }
          )}
        </div>
      )}
    </Box>
  );
};

export default function TotalComment({
  total,
  handleAction,
  message,
  identity,
  dataSource
}) {
  const { i18n } = useGlobal();
  const [reloadListUser, setReloadListUser] = React.useState(false);

  React.useEffect(() => {
    setReloadListUser(true);
  }, [total]);

  return (
    <Tooltip
      disableInteractive
      placement="top"
      title={
        <ListUser
          reloadListUser={reloadListUser}
          setReloadListUser={setReloadListUser}
          itemIdentity={identity}
          dataSource={dataSource}
        />
      }
    >
      {handleAction ? (
        <Wrapper
          data-testid="buttonToggleComment"
          role="button"
          tabIndex={-1}
          aria-label="Toggle Comment"
          onClick={() => handleAction('toggleItemComments')}
        >
          {i18n.formatMessage(
            { id: message || 'total_comment' },
            {
              value: total
            }
          )}
        </Wrapper>
      ) : (
        <div>
          {i18n.formatMessage(
            { id: message || 'total_comment' },
            {
              value: total
            }
          )}
        </div>
      )}
    </Tooltip>
  );
}

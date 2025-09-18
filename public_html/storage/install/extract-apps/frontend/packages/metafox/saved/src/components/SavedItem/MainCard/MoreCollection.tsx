import { Link, useGlobal } from '@metafox/framework';
import { Tooltip, Box } from '@mui/material';
import React from 'react';

const Collections = ({
  itemIdentity,
  reloadCollections,
  setReloadCollections,
  dataSource,
  total,
  excludeCollection = []
}) => {
  const { dispatch, i18n, useGetItem } = useGlobal();

  const [itemList, setItemList] = React.useState([]);
  const [otherCollection, setOtherCollection] = React.useState(0);
  const [loading, setLoading] = React.useState(false);
  const item = useGetItem(itemIdentity);

  const { id } = item || {};

  React.useLayoutEffect(() => {
    let mounted = true;
    setLoading(true);
    dispatch({
      type: 'getCommentCollections',
      payload: {
        id,
        saved_id: id,
        limit: 10,
        itemIdentity,
        forceReload: reloadCollections,
        dataSource
      },
      meta: {
        onSuccess: ({ data }) => {
          if (!mounted) return;

          setLoading(false);
          setItemList(data.filter(x => !excludeCollection.includes(x.id)));
          setOtherCollection(
            Math.max(total - data.length - excludeCollection.length, 0)
          );
          setReloadCollections(false);
        }
      }
    });

    return () => {
      mounted = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dispatch, itemIdentity]);

  if (loading)
    return (
      <Box sx={{ minWidth: '40px' }}>
        {i18n.formatMessage({ id: 'loading_dots' })}
      </Box>
    );

  return (
    <Box>
      {!!itemList.length &&
        itemList.map((item, index) => (
          <div key={index.toString()}>{item?.name}</div>
        ))}
      {!!otherCollection && (
        <div>
          {i18n.formatMessage(
            { id: 'and_value_more_dots' },
            { value: otherCollection }
          )}
        </div>
      )}
    </Box>
  );
};

export default function MoreCollection({
  total,
  identity,
  dataSource,
  excludeCollection = []
}) {
  const { i18n, dispatch } = useGlobal();
  const [reloadCollections, setReloadCollections] = React.useState(false);

  React.useEffect(() => {
    setReloadCollections(true);
  }, [total]);

  const handleClickMoreValue = () => {
    dispatch({ type: 'saved/viewCollectionItemSaved', payload: { identity } });
  };

  return (
    <span>
      <span>{i18n.formatMessage({ id: '_and_' })}</span>{' '}
      <Tooltip
        disableInteractive
        placement="top"
        title={
          <Collections
            reloadCollections={reloadCollections}
            setReloadCollections={setReloadCollections}
            itemIdentity={identity}
            dataSource={dataSource}
            total={total}
            excludeCollection={excludeCollection}
          />
        }
      >
        <Link
          color="text.primary"
          fontWeight="bold"
          onClick={handleClickMoreValue}
        >
          {i18n.formatMessage(
            { id: 'value_more' },
            {
              value: total - excludeCollection.length
            }
          )}
        </Link>
      </Tooltip>
    </span>
  );
}

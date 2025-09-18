import { Link, useGlobal, useResourceAction } from '@metafox/framework';
import { SavedItemProps as ItemProps } from '@metafox/saved/types';
import {
  ItemAction,
  ItemMedia,
  ItemSubInfo,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  UserName
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, Button, styled } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';
import MoreCollection from './MoreCollection';
import { APP_SAVED, RESOURCE_SAVED_LIST } from '@metafox/saved/constant';

const name = 'SavedItemView';
const UnOpenedDot = styled('span', { name, slot: 'UnOpenedDot' })(
  ({ theme }) => ({
    display: 'inline-block',
    width: 6,
    height: 6,
    backgroundColor: theme.palette.primary.main,
    borderRadius: 4,
    marginRight: theme.spacing(0.5)
  })
);
const ItemTitleStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  overflow: 'hidden'
}));

export default function SavedItemView({
  item,
  identity,
  handleAction,
  state,
  itemProps,
  user: userSaved,
  wrapAs,
  wrapProps
}: ItemProps) {
  const {
    ItemActionMenu,
    i18n,
    assetUrl,
    dispatch,
    useGetItem,
    dialogBackend,
    useSession,
    usePageParams
  } = useGlobal();
  const { user } = useSession();
  const { collection_id } = usePageParams();
  const identityFirstCollection = `${APP_SAVED}.entities.${RESOURCE_SAVED_LIST}.${item?.default_collection_id}`;
  const firstCollection = useGetItem(identityFirstCollection);

  const identityCollection = `${APP_SAVED}.entities.${RESOURCE_SAVED_LIST}.${collection_id}`;
  const collectionSelect = useGetItem(identityCollection);

  const userOwnerItem = useGetItem(item?.owner);
  const dataSourceCollections = useResourceAction(
    APP_SAVED,
    RESOURCE_SAVED_LIST,
    'viewAll'
  );

  if (!item || !userSaved) return null;

  const { menuName = 'itemActionMenu' } = itemProps;

  const imgSrc = getImageSrc(
    item?.image,
    '240,200x200',
    assetUrl('saved.no_image')
  );

  const handleMarkOpened = () => {
    dispatch({
      type: 'saved/markAsOpened',
      payload: { identity, showFeedbackSuccess: false }
    });
  };

  const handleAddToCollection = () => {
    dialogBackend.present({
      component: 'saved.dialog.saveToCollection',
      props: {
        item
      }
    });
  };

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={camelCase(`item ${item.resource_name}`)}
    >
      <Box onClick={handleMarkOpened}>
        <ItemMedia
          src={imgSrc}
          link={item.link}
          alt={item.title}
          backgroundImage
        />
      </Box>
      <ItemText>
        <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
          <ItemTitleStyled>
            <Box>{!item.is_opened ? <UnOpenedDot /> : null}</Box>
            <ItemTitle onClick={handleMarkOpened}>
              <Link to={item.link}>{item.title}</Link>
            </ItemTitle>
          </ItemTitleStyled>
          {itemProps.showActionMenu ? (
            <ItemAction>
              <ItemActionMenu
                menuName={menuName}
                identity={identity}
                icon={'ico-dottedmore-vertical-o'}
                state={state}
                handleAction={handleAction}
                size="smallest"
              />
            </ItemAction>
          ) : null}
        </Box>
        <ItemSubInfo sx={{ color: 'text.secondary', mt: 1 }}>
          <Link
            color="inherit"
            to={userOwnerItem?.link}
            children={userOwnerItem?.full_name}
            hoverCard={`/${userOwnerItem.module_name}/${userOwnerItem?.id}`}
          />
          {item.item_type_name
            ? i18n.formatMessage({ id: item.item_type_name })
            : null}
          {item.belong_to_collection ? (
            <span>
              {i18n.formatMessage({ id: 'added_to' })}{' '}
              <Link
                to={`/saved/list/${item.default_collection_id}`}
                color="text.primary"
                fontWeight="bold"
              >
                {firstCollection?.name || item.default_collection_name}
              </Link>{' '}
              {item.statistic?.total_collection > 1 ? (
                <MoreCollection
                  identity={identity}
                  total={item.statistic?.total_collection}
                  dataSource={dataSourceCollections}
                  excludeCollection={[item.default_collection_id]}
                />
              ) : null}
            </span>
          ) : null}
        </ItemSubInfo>
        {collection_id && collectionSelect?.statistic?.total_member > 1 && (
          <Box mt={1}>
            {i18n.formatMessage(
              { id: 'added_by_name' },
              {
                name:
                  user?.id === userSaved?.id ? (
                    'you'
                  ) : (
                    <UserName
                      sx={{ fontWeight: 'bold' }}
                      to={userSaved?.link}
                      user={userSaved}
                    />
                  )
              }
            )}
          </Box>
        )}

        <Box sx={{ mt: 2 }}>
          {item?.extra?.can_add_to_list ? (
            <Button
              variant="outlined"
              size="small"
              color="primary"
              onClick={handleAddToCollection}
            >
              <LineIcon icon="ico-folder-plus-o" sx={{ mr: 1, fontSize: 15 }} />
              {i18n.formatMessage({ id: 'add_to_collection' })}
            </Button>
          ) : null}
        </Box>
      </ItemText>
    </ItemView>
  );
}

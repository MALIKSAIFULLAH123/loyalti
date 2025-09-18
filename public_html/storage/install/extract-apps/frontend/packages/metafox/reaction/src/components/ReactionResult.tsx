/**
 * @type: service
 * name: ReactionResult
 * chunkName: reaction
 */
import { getItemSelector, GlobalState, useGlobal } from '@metafox/framework';
import { Box, styled, Tooltip } from '@mui/material';
import React, { useState } from 'react';
import { useSelector } from 'react-redux';
import ReactionIcon from '../containers/ReactionIcon';
import { getReactionItemSelector } from '../selectors/geReactionItem';
import { MostReactionsProps, ReactionItem } from '../types';
import useStyles from './ReactionResult.styles';
import { LIMIT_SHOW_REACTION } from '../constants';
import { getIdentityFromId } from '../utils';
import { isEmpty } from 'lodash';

export interface StyledWrapperProps {
  size?: 'md' | 'sm';
}

const ItemReaction = styled('span', {
  name: 'ItemReaction'
})(({ theme }) => ({
  display: 'inline-flex',
  boxSizing: 'content-box',
  border: '1px solid',
  borderColor: theme.palette.background.paper,
  borderRadius: '100%',
  lineHeight: 0,
  width: '100%',
  height: '100%',
  '& img': {
    width: '100%',
    height: '100%',
    borderRadius: '100%'
  }
}));

const Wrapper = styled('span', {
  name: 'ReactResultWrapper',
  shouldForwardProp: prop => prop !== 'size'
})<StyledWrapperProps>(({ theme, size }) => ({
  display: 'inline-flex',
  alignItems: 'center',
  ...(size &&
    size === 'md' && {
      '& .itemReaction': {
        width: '24px',
        height: '24px',
        '& + .itemReaction': {
          marginLeft: '-4px'
        }
      },
      '& .listReaction': {
        marginRight: theme.spacing(1.5)
      }
    }),
  ...(size &&
    size === 'sm' && {
      '& .itemReaction': {
        width: 16,
        height: 16,
        '& + .itemReaction': {
          marginLeft: '-3px'
        }
      },
      '& .listReaction': {
        marginRight: theme.spacing(0.25)
      }
    })
}));

const TitleReaction = styled('div', {
  name: 'TitleReaction'
})(({ theme }) => ({
  fontWeight: theme.typography.fontWeightBold,
  fontSize: theme.mixins.pxToRem(13)
}));

const ListUser = ({ reactId: react_id, itemIdentity, onUpdate }) => {
  const [itemList, setItemList] = useState([]);
  const [leftUser, setLeftUser] = useState(0);
  const [loading, setLoading] = useState(false);

  const { dispatch, i18n } = useGlobal();
  const item = useSelector((state: GlobalState) =>
    getItemSelector(state, itemIdentity)
  );
  const react = useSelector<GlobalState, ReactionItem>((state: GlobalState) =>
    getReactionItemSelector(state, react_id)
  );

  const { item_type, item_id, resource_name, like_item_id, like_type_id, id } =
    item || {};

  React.useLayoutEffect(() => {
    let mounted = true;
    setLoading(true);
    // TODO: update item_type when move to api v5
    dispatch({
      type: 'react/getReactedListUser',
      payload: {
        id,
        item_id,
        item_type,
        resource_name,
        like_type_id,
        like_item_id,
        react_id,
        limit: 10
      },
      meta: {
        onSuccess: ({ data, meta }) => {
          if (!mounted) return;

          onUpdate(Math.random());
          setLoading(false);
          setItemList(data);
          setLeftUser(Math.max(meta?.total - data.length, 0));
        }
      }
    });

    return () => {
      mounted = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dispatch, item_id, item_type, onUpdate, react_id, resource_name]);

  if (loading) return <Box>{i18n.formatMessage({ id: 'loading_dots' })}</Box>;

  return (
    <div>
      <TitleReaction>{react?.title}</TitleReaction>
      {!!itemList.length &&
        itemList.map((item, index) => (
          <div key={index.toString()}>{item.user.full_name}</div>
        ))}
      {!!leftUser && (
        <div>
          {i18n.formatMessage(
            { id: 'and_value_more_dots' },
            { value: leftUser }
          )}
        </div>
      )}
    </div>
  );
};

export default function ReactionResult({
  data,
  handleAction,
  limit = LIMIT_SHOW_REACTION,
  total,
  size = 'md',
  identity
}: MostReactionsProps) {
  const { dispatch, isMobile, useGetItem } = useGlobal();
  const classes = useStyles();
  const [, shouldUpdate] = useState(0);
  const item = useGetItem(identity);

  if (!item.extra?.can_view_reaction || isEmpty(data)) return null;

  const onClickReactIcon = (identity: string, react_id?: string) => {
    const reactIdentity = getIdentityFromId(react_id);

    dispatch({
      type: 'showPeopleWhoReactThisItem',
      payload: { identity, reactIdentity }
    });
  };

  return (
    <Wrapper data-testid="reactionResult" size={size} role="button">
      <div className={classes.listReaction}>
        {data?.slice(0, limit).map((reaction, index) => (
          <Tooltip
            disableInteractive
            enterDelay={1000}
            title={
              isMobile ? null : (
                <ListUser
                  reactId={reaction?.id}
                  itemIdentity={identity}
                  onUpdate={shouldUpdate}
                />
              )
            }
            key={index.toString()}
          >
            <ItemReaction
              className={'itemReaction'}
              onClick={() => onClickReactIcon(identity, reaction?.id)}
            >
              <ReactionIcon
                identity={getIdentityFromId(reaction?.id)}
                classes={classes}
                key={reaction?.id}
              />
            </ItemReaction>
          </Tooltip>
        ))}
      </div>
      {total ? (
        <Tooltip
          disableInteractive
          title={
            isMobile ? null : (
              <ListUser
                reactId={0}
                itemIdentity={identity}
                onUpdate={shouldUpdate}
              />
            )
          }
        >
          <span
            className={classes.totalReaction}
            onClick={() => onClickReactIcon(identity)}
          >
            {total}
          </span>
        </Tooltip>
      ) : null}
    </Wrapper>
  );
}

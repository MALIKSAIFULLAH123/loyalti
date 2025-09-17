import { useGlobal } from '@metafox/framework';
import { FriendSuggestionCollectionShape } from '@metafox/friend/types';
import { colorHash, getImageSrc, shortenFullName } from '@metafox/utils';
import { Avatar, CircularProgress, styled } from '@mui/material';
import React from 'react';

const name = 'SuggestionList';

const RootNoFound = styled('div', { name, slot: 'RootNoFound' })(
  ({ theme }) => ({
    minHeight: '100px',
    textAlign: 'center'
  })
);
const Root = styled('div', { name, slot: 'Root' })(({ theme }) => ({
  minHeight: '100px',
  margin: theme.spacing(1, -1, 0)
}));

type SuggestionListProps = {
  classes?: Record<
    'smallAvatar' | 'tagItem' | 'userName' | 'suggestionListRoot',
    string
  >;
  onItemClick: (item: unknown) => void;
  excludeIds?: Array<number>;
  isFullFriend?: boolean;
  postAsParent?: boolean;
  parentUser?: any;
} & FriendSuggestionCollectionShape;

export default function SuggestionList(props: SuggestionListProps) {
  const { dispatch, i18n } = useGlobal();
  const {
    text,
    item_id,
    data,
    loaded,
    onItemClick,
    classes,
    excludeIds,
    isFullFriend,
    postAsParent,
    parentUser
  } = props;
  const extraParams = React.useMemo(() => {
    return isFullFriend
      ? {
          excludeIds
        }
      : {
          item_type: 'photo',
          item_id
        };
  }, [excludeIds, item_id, isFullFriend]);

  React.useEffect(() => {
    dispatch({
      type: 'friend/suggestions/LOAD',
      payload: {
        text,
        ...extraParams,
        user_id: postAsParent && parentUser?.id,
        owner_id: parentUser?.id
      }
    });
  }, [dispatch, text, extraParams]);

  if (!loaded) {
    return (
      <RootNoFound
        sx={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          cursor: 'default'
        }}
        onClick={e => e.stopPropagation()}
      >
        <CircularProgress color="secondary" size={32} />
      </RootNoFound>
    );
  }

  if (!data?.length) {
    return (
      <RootNoFound onClick={e => e.stopPropagation()}>
        <div style={{ padding: 16 }}>
          {i18n.formatMessage({ id: 'no_people_found' })}
        </div>
      </RootNoFound>
    );
  }

  return (
    <Root>
      {data.map(option => (
        <div
          onClick={evt => {
            evt.preventDefault();
            evt.stopPropagation();
            onItemClick(option);
          }}
          className={classes.tagItem}
          key={option.id.toString()}
        >
          <Avatar
            src={getImageSrc(option.image, '240')}
            children={shortenFullName(option.label)}
            alt={option.label}
            className={classes.smallAvatar}
            style={{
              backgroundColor: colorHash.hex(shortenFullName(option.label))
            }}
          />
          <span className={classes.userName}>{option.label}</span>
        </div>
      ))}
    </Root>
  );
}

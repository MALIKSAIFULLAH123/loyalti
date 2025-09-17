import { useGlobal } from '@metafox/framework';
import { Popper, styled } from '@mui/material';
import * as React from 'react';
import SuggestionList from './ConnectedSuggestionList';

const Text = styled('input', {
  name: 'Text'
})(({ theme }) => ({
  '&::placeholder': {
    color: theme.palette.text.hint
  }
}));

export type Props = {
  classes: any;
  onItemClick: any;
  open: boolean;
  identity: string;
  anchorRef: React.MutableRefObject<HTMLDivElement>;
  excludeIds?: Array<number>;
  isFullFriend?: boolean;
  postAsParent?: boolean;
  parentUser?: any;
};

export default function Suggestion({
  classes,
  onItemClick,
  anchorRef,
  identity,
  isFullFriend,
  excludeIds,
  postAsParent,
  parentUser
}: Props) {
  const { i18n } = useGlobal();
  const [text, setText] = React.useState<string>('');

  const onTextChanged = (text: string) => {
    setText(text);
  };

  return (
    <Popper
      id="suggest-friends"
      open={Boolean(anchorRef.current)}
      disablePortal
      anchorEl={anchorRef.current}
      placement="bottom"
      className={classes.popper}
      onClick={e => e.stopPropagation()}
    >
      <div className={classes.inputBaseBox}>
        <Text
          type="text"
          placeholder={i18n.formatMessage({ id: 'type_any_name' })}
          onChange={evt => onTextChanged(evt.target.value)}
          autoFocus
          onBlur={e => e.target.focus()}
        />
      </div>
      <SuggestionList
        onItemClick={onItemClick}
        text={text}
        classes={classes}
        identity={identity}
        isFullFriend={isFullFriend}
        excludeIds={excludeIds}
        postAsParent={postAsParent}
        parentUser={parentUser}
      />
    </Popper>
  );
}

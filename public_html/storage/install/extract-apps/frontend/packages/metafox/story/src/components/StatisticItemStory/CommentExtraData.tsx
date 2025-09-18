import { useGlobal } from '@metafox/framework';
import { TruncateText } from '@metafox/ui';
import { styled } from '@mui/material';
import { isString } from 'lodash';
import React from 'react';

const name = 'CommentExtraData';

const BubbleExtraData = styled('div', { name, slot: 'bubbleExtraData' })(
  ({ theme }) => ({
    display: 'inline'
  })
);

export default function CommentExtraData(props: any) {
  const { useGetItem, i18n } = useGlobal();
  const { extra_data: data, text } = props;

  let extra_data = data;

  if (isString(data)) {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    extra_data = useGetItem(data);
  }

  const { extra_type } = Object.assign({}, extra_data);

  if (!extra_data) return null;

  let content = null;
  switch (extra_type) {
    case 'photo':
    case 'storage_file':
      content = i18n.formatMessage({ id: 'comment_add_photo' });
      break;
    case 'sticker':
      if (text) {
        content = null;
      } else {
        content = i18n.formatMessage({ id: 'comment_add_sticker' });
      }

      break;
  }

  if (!content) return null;

  return (
    <TruncateText
      variant="body1"
      lines={1}
      style={{ fontSize: '13px', color: 'rgba(255, 255, 255, 0.9)' }}
    >
      <BubbleExtraData>{content}</BubbleExtraData>
    </TruncateText>
  );
}

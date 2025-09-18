import { MsgEmbedShape } from '@metafox/chatplus/types';
import { triggerClick } from '@metafox/chatplus/utils';
import { TruncateText } from '@metafox/ui';
import { styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';

const name = 'MsgEmbed';
const UIMsgEmbed = styled('div', { name, slot: 'uiMsgEmbed' })(({ theme }) => ({
  marginTop: '4px',
  marginBottom: '8px',
  width: '400px',
  maxWidth: '100%',
  cursor: 'pointer'
}));
const UIMsgEmbedOuter = styled('div', { name, slot: 'uiMsgEmbedOuter' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    borderRadius: '8px',
    border: theme.mixins.border('secondary'),
    color: '#555555'
  })
);
const ItemMedia = styled('div', { name, slot: 'ItemMedia' })(({ theme }) => ({
  width: '100%'
}));
const ItemMediaSrc = styled('div', { name, slot: 'ItemMediaSrc' })(
  ({ theme }) => ({
    width: '100%',
    display: 'block',
    position: 'relative',
    backgroundSize: 'cover',
    backgroundPosition: 'center center',
    backgroundRepeat: 'no-repeat',
    backgroundOrigin: 'border-box',
    border: '1px solid rgba(0, 0, 0, 0.1)',
    borderRadius: '8px 8px 0 0',
    backgroundColor: 'transparent',
    // border: 'none',
    borderBottom: '1px solid rgba(0, 0, 0, 0.1)',
    '&:before': {
      content: "''",
      display: 'block',
      paddingBottom: '56%'
    }
  })
);
const ItemInner = styled('div', { name, slot: 'ItemInner' })(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  padding: '8px'
}));
const ItemTitle = styled(TruncateText, { name, slot: 'ItemTitle' })(
  ({ theme }) => ({
    marginBottom: '2px',
    color:
      theme.palette.mode === 'light'
        ? theme.palette.grey['900']
        : theme.palette.text.primary
  })
);
const ItemUrl = styled(TruncateText, { name, slot: 'ItemUrl' })(
  ({ theme }) => ({
    marginBottom: '2px',
    color: theme.palette.text.secondary
  })
);
const ItemDescription = styled(TruncateText, { name, slot: 'ItemDescription' })(
  ({ theme }) => ({
    color:
      theme.palette.mode === 'light'
        ? theme.palette.grey['900']
        : theme.palette.text.primary
  })
);

interface Props extends MsgEmbedShape {}

export default function MsgEmbed({ url, meta, parsedUrl, ignoreParse }: Props) {
  const full_fill =
    !ignoreParse &&
    ((meta && !isEmpty(meta)) || (parsedUrl && !isEmpty(parsedUrl)));

  if (!full_fill) {
    return null;
  }

  const {
    ogTitle,
    ogDescription,
    ogImage,
    oembedThumbnailUrl,
    oembedTitle,
    oembedDescription
  } = meta;

  const host = parsedUrl.host;
  const title = ogTitle || oembedTitle;
  const desc = ogDescription || oembedDescription;

  return (
    <UIMsgEmbed>
      <UIMsgEmbedOuter onClick={() => triggerClick(url, true)}>
        {ogImage || oembedThumbnailUrl ? (
          <ItemMedia>
            <ItemMediaSrc
              style={{
                backgroundImage: `url(${
                  ogImage ? ogImage : oembedThumbnailUrl
                })`
              }}
            />
          </ItemMedia>
        ) : (
          <ItemMedia>
            <span className={'item-media-src media-default'} />
          </ItemMedia>
        )}
        <ItemInner>
          {title ? (
            <ItemTitle lines={2} variant="h6">
              {title}
            </ItemTitle>
          ) : null}
          <ItemUrl lines={2} variant="body2">
            {host}
          </ItemUrl>
          {desc ? (
            <ItemDescription lines={2} variant="h6">
              {desc}
            </ItemDescription>
          ) : null}
        </ItemInner>
      </UIMsgEmbedOuter>
    </UIMsgEmbed>
  );
}

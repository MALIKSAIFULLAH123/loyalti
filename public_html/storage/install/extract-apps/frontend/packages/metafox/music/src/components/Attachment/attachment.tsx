import { useGetItems, useGlobal } from '@metafox/framework';
import { AttachmentItem } from '@metafox/ui';
import { styled, Box, SxProps } from '@mui/material';
import React from 'react';

const AttachmentTitle = styled('div', { name: 'attachmentTitle' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(18),
    color: theme.palette.text.secondary,
    fontWeight: theme.typography.fontWeightBold
  })
);

const Attachment = styled('div', { name: 'attachment' })(({ theme }) => ({
  width: '100%',
  display: 'flex',
  flexWrap: 'wrap',
  justifyContent: 'space-between',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));
const AttachmentItemWrapper = styled('div', {
  name: 'attachmentItemWrapper'
})(({ theme }) => ({
  marginTop: theme.spacing(2),
  flexGrow: 0,
  flexShrink: 0,
  flexBasis: 'calc(50% - 8px)',
  minWidth: 300,
  maxWidth: '100%'
}));

export type AttachmentsType = {
  attachments: string[];
  size?: 'small' | 'large' | 'mini';
  sx?: SxProps;
};

const AttachmentFile = ({
  attachments: identities,
  size,
  sx
}: AttachmentsType) => {
  const { i18n } = useGlobal();
  const attachments = useGetItems(identities);

  if (!attachments.length) return null;

  return (
    <Box mt={2} sx={sx}>
      <AttachmentTitle>
        {i18n.formatMessage({ id: 'attachments' })}
      </AttachmentTitle>
      <Attachment>
        {attachments.map((item, index) => (
          <AttachmentItemWrapper key={item.id.toString()}>
            <AttachmentItem
              fileName={item.file_name}
              downloadUrl={item.download_url}
              isImage={item.is_image}
              fileSizeText={item.file_size_text}
              size={size}
              image={item?.image}
              identity={item?._identity}
              index={index}
            />
          </AttachmentItemWrapper>
        ))}
      </Attachment>
    </Box>
  );
};

export default AttachmentFile;

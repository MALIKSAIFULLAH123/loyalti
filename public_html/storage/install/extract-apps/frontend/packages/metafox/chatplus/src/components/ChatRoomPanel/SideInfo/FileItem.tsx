import { formatBytes, triggerClick } from '@metafox/chatplus/utils';
import { useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText } from '@metafox/ui';
import { Button, styled, Tooltip, Typography } from '@mui/material';
import React from 'react';

const Root = styled('div')(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  justifyContent: 'space-between',
  alignItems: 'center',
  height: theme.spacing(5.75),
  marginBottom: theme.spacing(1.25)
}));
const WrapperInfo = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  overflow: 'hidden'
}));
const WrapperFilesName = styled('div')(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  overflow: 'hidden',
  marginRight: theme.spacing(0.5)
}));

const ButtonIcon = styled(Button)(({ theme }) => ({
  color: theme.palette.primary.main,
  border: theme.mixins.border('primary'),
  minWidth: theme.spacing(5)
}));
const WrapperLineIcon = styled(LineIcon)(({ theme }) => ({
  color: theme.palette.grey['600'],
  fontSize: theme.spacing(5.75),
  marginRight: theme.spacing(1)
}));

interface Props {
  name: string;
  size: number;
  [key: string]: any;
}

function FileItem(props: Props) {
  const { chatplus, i18n } = useGlobal();

  const size = formatBytes(props.size || 0);

  return (
    <Root>
      <WrapperInfo>
        <WrapperLineIcon icon="ico-file-zip-o" />
        <WrapperFilesName>
          <TruncateText lines={1} variant="h5">
            {props.name}
          </TruncateText>
          <Typography>{size}</Typography>
        </WrapperFilesName>
      </WrapperInfo>
      <Tooltip
        title={i18n.formatMessage({ id: 'download' })}
        PopperProps={{
          disablePortal: true
        }}
        placement="top"
      >
        <ButtonIcon
          onClick={() =>
            triggerClick(
              chatplus.sanitizeRemoteFileUrl(props?.url),
              false,
              true
            )
          }
        >
          <LineIcon icon="ico-download" />
        </ButtonIcon>
      </Tooltip>
    </Root>
  );
}

export default FileItem;

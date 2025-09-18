/**
 * @type: dialog
 * name: event.dialog.generateInviteLinkDialog
 */
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText } from '@metafox/ui';
import { Button, Skeleton, styled, Tooltip } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';

const name = 'GenerateLinkField';
const InputCustomStyled = styled('div', { name })(({ theme }) => ({
  padding: theme.spacing(0.5, 1.75),
  margin: theme.spacing(2),
  border: theme.mixins.border('secondary'),
  borderRadius: theme.spacing(0.5),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between'
}));
const WrapperStyled = styled('div', { name })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  overflow: 'hidden',
  width: '90%'
}));

const ReloadIcon = styled(LineIcon, { name: 'ReloadIcon' })(({ theme }) => ({
  fontSize: theme.spacing(1.875),
  color:
    theme.palette.mode === 'light'
      ? theme.palette.primary.main
      : theme.palette.primary.main,
  cursor: 'pointer'
}));

const CopyButton = styled(Button, { name: 'ReloadButton' })(({ theme }) => ({
  fontWeight: 'bold',
  height: theme.spacing(4),
  marginLeft: theme.spacing(1)
}));
const CodeText = styled(TruncateText, { name: 'CodeText' })(({ theme }) => ({
  color: theme.palette.text.hint,
  overflow: 'hidden'
}));
const WrapperLoading = styled('div', { name: 'WrapperLoading' })(
  ({ theme }) => ({
    margin: theme.spacing(2),
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
  })
);
const ButtonIcon = styled(Button)(() => ({
  '& span': {
    margin: 0
  }
}));

const GenerateInviteLinkDialog = (item: any) => {
  const { useDialog, i18n, dispatch, copyToClipboard, toastBackend } =
    useGlobal();
  const { dialogProps } = useDialog();
  const [data, setData] = React.useState(null);

  const fetch = type => {
    dispatch({
      type,
      payload: item,
      meta: {
        onSuccess: value => {
          setData(value);
        },
        onFailure: () => {}
      }
    });
  };

  React.useEffect(() => {
    if (item) {
      fetch('event/getCode');
    }
  }, []);

  const handleCopy = () => {
    copyToClipboard(data.url);
    toastBackend.success(i18n.formatMessage({ id: 'copied_to_clipboard' }));
  };

  const handleReloadCode = () => {
    fetch('event/refreshCode');
  };

  return (
    <Dialog
      {...dialogProps}
      maxWidth="sm"
      fullWidth
      data-testid={camelCase('dialog generate_invite_link')}
    >
      <DialogTitle>
        {i18n.formatMessage({ id: 'generate_invite_link' })}
      </DialogTitle>
      <DialogContent variant="fitScroll">
        {!data ? (
          <WrapperLoading>
            <Skeleton variant="rounded" width={'100%'} height={35} />
          </WrapperLoading>
        ) : (
          <InputCustomStyled>
            <WrapperStyled>
              <CodeText lines={1}>{data.url}</CodeText>
            </WrapperStyled>
            {data?.status ? null : (
              <Tooltip
                title={i18n.formatMessage({ id: 'refresh' })}
                placement="top"
              >
                <ButtonIcon
                  onClick={handleReloadCode}
                  variant="outlined"
                  size="small"
                  startIcon={<ReloadIcon icon="ico-refresh-o" />}
                  data-testid={camelCase('button refresh')}
                />
              </Tooltip>
            )}
            <CopyButton
              variant="outlined"
              onClick={handleCopy}
              data-testid={camelCase('button copy')}
            >
              {i18n.formatMessage({ id: 'copy' })}
            </CopyButton>
          </InputCustomStyled>
        )}
      </DialogContent>
    </Dialog>
  );
};

export default GenerateInviteLinkDialog;

import { useDialog } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import isString from '@metafox/scrollbars/utils/isString';
import { LineIcon } from '@metafox/ui';
import {
  Button,
  DialogTitle as MuiDialogTitle,
  DialogTitleProps,
  IconButton
} from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

type Props = DialogTitleProps & {
  backIcon?: string;
  closeIcon?: string;
  enableBack?: boolean;
  disableClose?: boolean;
  enableDone?: boolean;
  backDialogIcon?: boolean;
  onBackClick?: () => void;
  onCancel?: () => void;
  onDoneClick?: () => void;
};

const StyledTitle = styled('div')({
  flex: 1,
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis'
});

const CloseButton = styled(IconButton, { name: 'MuiDialogClose' })(() => ({
  marginLeft: 'auto',
  transform: 'translate(4px,0)'
}));

const BackButton = styled(IconButton, { name: 'MuiDialogBack' })(() => ({
  transform: 'translate(-7px,0)'
}));

const DialogTitle = React.forwardRef((props: Props, ref) => {
  const {
    children,
    enableDone,
    disableClose,
    enableBack = false,
    backIcon = 'ico-arrow-left',
    closeIcon = 'ico-close',
    onBackClick,
    onDoneClick,
    onCancel
  } = props;
  const { i18n } = useGlobal();
  const { closeDialog } = useDialog();

  return (
    <MuiDialogTitle ref={ref}>
      {enableBack ? (
        <BackButton
          size="small"
          onClick={onBackClick ?? closeDialog}
          role="button"
          id="back"
          data-testid="buttonBack"
        >
          <LineIcon icon={backIcon} />
        </BackButton>
      ) : null}
      {React.Children.toArray(children).map(child => {
        return isString(child) ? (
          <StyledTitle
            role="heading"
            aria-level={1}
            key="text"
            id="dialog title"
            children={child}
          />
        ) : (
          child
        );
      })}
      {!disableClose ? (
        <CloseButton
          size="small"
          onClick={onCancel ?? closeDialog}
          data-testid="buttonClose"
          role="button"
        >
          <LineIcon icon={closeIcon} />
        </CloseButton>
      ) : null}
      {enableDone ? (
        <Button
          color="primary"
          onClick={onDoneClick}
          role="button"
          data-testid="buttonDone"
        >
          {i18n.formatMessage({ id: 'done' })}
        </Button>
      ) : null}
    </MuiDialogTitle>
  );
});

export default DialogTitle;

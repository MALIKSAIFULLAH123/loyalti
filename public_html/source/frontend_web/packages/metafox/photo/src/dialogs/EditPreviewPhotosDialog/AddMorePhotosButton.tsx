import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, ButtonProps } from '@mui/material';
import React from 'react';
import useHandleFeedMediaFile from '@metafox/photo/hooks/useHandleFeedMediaFile';
import useFeedMediaConfig from '@metafox/photo/hooks/useFeedMediaConfig';
type Props = {
  setItems: any;
  variant?: ButtonProps['variant'];
  totalCurrent?: number;
  limit?: number;
  items: Record<string, any>[];
  parentUser: Record<string, any>;
  icon?: string;
};
export default function AddMorePhotosButton({
  variant = 'outlined',
  setItems,
  items,
  parentUser,
  icon = 'ico-photos-plus-o'
}: Props) {
  const { i18n } = useGlobal();
  const inputRef = React.useRef<HTMLInputElement>();
  const [handleFile] = useHandleFeedMediaFile({
    parentUser
  });
  const { acceptTypes } = useFeedMediaConfig({
    parentUser
  });

  const onClick = React.useCallback(() => {
    inputRef?.current?.click();
  }, [inputRef]);

  const clear = () => {
    if (inputRef?.current) {
      inputRef.current.value = null;
    }
  };

  const handleChange = () => {
    const files = inputRef?.current?.files;

    handleFile(files, items, values => {
      setItems(values);
    });
    clear();
  };

  return (
    <>
      <Button
        color="primary"
        onClick={onClick}
        variant={variant}
        startIcon={<LineIcon icon={icon} />}
      >
        {i18n.formatMessage({ id: 'add_photos' })}
      </Button>
      <input
        onChange={handleChange}
        multiple
        ref={inputRef}
        className="srOnly"
        type="file"
        accept={acceptTypes}
      />
    </>
  );
}

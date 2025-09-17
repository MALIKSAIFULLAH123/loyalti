import React from 'react';

type Props = {
  imageRef: React.MutableRefObject<HTMLImageElement>;
  onAddPhotoTag: (data: any) => void;
  onRemovePhotoTag?: (data: any) => void;
  ready: boolean;
};

export default function useTagOnImage({
  imageRef,
  onAddPhotoTag,
  onRemovePhotoTag,
  ready
}: Props) {
  const [offset, setOffset] = React.useState({ px: 0, py: 0 });
  const [openTagBox, setOpenTagBox] = React.useState<boolean>(false);
  const [tagging, setTagging] = React.useState<boolean>(false);
  const [currentSize, setCurrentSize] = React.useState({
    width: 0,
    height: 0
  });

  const onLoad = e => {
    const rect = e.getBoundingClientRect();
    setCurrentSize({ width: rect.width, height: rect.height });
  };

  const onClickImageBox = (e: React.MouseEvent<HTMLDivElement>) => {
    if (!tagging) return;

    const rect = e.currentTarget.getBoundingClientRect();

    const px: number =
      (Math.max(Math.min(e.clientX - rect.left, currentSize.width - 50), 50) /
        currentSize.width) *
      100;
    const py: number =
      (Math.max(Math.min(e.clientY - rect.top, currentSize.height - 50), 50) /
        currentSize.height) *
      100;
    setOffset({ px, py });

    if (tagging) {
      setOpenTagBox(true);
    }
  };

  const chooseFriendToTag = (content: unknown) => {
    const { px, py } = offset;
    const newTaggedFriend = { content, px, py };
    onAddPhotoTag(newTaggedFriend);
    setOpenTagBox(false);
  };

  React.useEffect(() => {
    if (ready && imageRef?.current) {
      onLoad(imageRef?.current);
    }
  }, [ready, imageRef?.current]);

  return {
    chooseFriendToTag,
    onClickImageBox,
    tagging,
    setTagging,
    offsetTag: offset,
    openTagBox
  };
}

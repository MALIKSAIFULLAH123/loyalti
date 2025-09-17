import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import { $wrapNodeInElement, mergeRegister } from '@lexical/utils';
import {
  $createParagraphNode,
  $createRangeSelection,
  $getSelection,
  $insertNodes,
  $isNodeSelection,
  $isRootOrShadowRoot,
  $setSelection,
  COMMAND_PRIORITY_EDITOR,
  COMMAND_PRIORITY_HIGH,
  COMMAND_PRIORITY_LOW,
  createCommand,
  DRAGOVER_COMMAND,
  DRAGSTART_COMMAND,
  DROP_COMMAND,
  LexicalCommand,
  LexicalEditor
} from 'lexical';
import { useEffect, useRef, useState } from 'react';
import * as React from 'react';
import { CAN_USE_DOM } from '@metafox/utils';
import {
  $createImageNode,
  $isImageNode,
  ImageNode,
  ImagePayload
} from '@metafox/lexical/nodes/ImageNode';
import { Button, Box } from '@mui/material';
import { useGlobal } from '@metafox/framework';
import UploadForm from './ImagePicker/UploadForm';
import UrlForm from './ImagePicker/UrlForm';
import { isNil } from 'lodash';

export type InsertImagePayload = Readonly<ImagePayload>;

// eslint-disable-next-line no-confusing-arrow
const getDOMSelection = (targetWindow: Window | null): Selection | null =>
  CAN_USE_DOM ? (targetWindow || window).getSelection() : null;

export const INSERT_IMAGE_COMMAND: LexicalCommand<InsertImagePayload> =
  createCommand('INSERT_IMAGE_COMMAND');

const createImage = (url: string) =>
  new Promise((resolve, reject) => {
    const image = new Image();
    image.addEventListener('load', () => resolve(image));
    image.addEventListener('error', error => reject(error));
    image.src = url;
  });

const convertValue2Number = (value: any) => {
  return !isNil(value) && !isNaN(value) ? parseFloat(value) : value;
};

export function InsertImageUriDialogBody({
  onClick
}: {
  onClick: (payload: InsertImagePayload) => void;
}) {
  const { dialogBackend, i18n } = useGlobal();

  const onSubmit = async ({ src, altText, width, height }) => {
    try {
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      const image = await createImage(src);

      onClick({
        altText,
        src,
        width: convertValue2Number(width),
        height: convertValue2Number(height)
      });
    } catch (err) {
      dialogBackend.alert({
        message: i18n.formatMessage({ id: 'the_url_is_invalid' })
      });
    }
  };

  return <UrlForm onSubmit={onSubmit} />;
}

export function InsertImageUploadedDialogBody({
  onClick
}: {
  onClick: (payload: InsertImagePayload) => void;
}) {
  const { dialogBackend, i18n } = useGlobal();

  const onSubmit = async ({ src, altText, width, height }) => {
    try {
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      const image = await createImage(src);

      onClick({
        altText,
        src,
        width: convertValue2Number(width),
        height: convertValue2Number(height)
      });
    } catch (err) {
      dialogBackend.alert({
        message: i18n.formatMessage({ id: 'error_invalid_file_type' })
      });
    }
  };

  return <UploadForm onSubmit={onSubmit} />;
}

export function InsertImageDialog({
  activeEditor,
  onClose
}: {
  activeEditor: LexicalEditor;
  onClose: () => void;
}): JSX.Element {
  const [mode, setMode] = useState<null | 'url' | 'file'>(null);
  const hasModifier = useRef(false);
  const { i18n } = useGlobal();

  useEffect(() => {
    hasModifier.current = false;
    const handler = (e: KeyboardEvent) => {
      hasModifier.current = e.altKey;
    };
    document.addEventListener('keydown', handler);

    return () => {
      document.removeEventListener('keydown', handler);
    };
  }, [activeEditor]);

  const onClick = (payload: InsertImagePayload) => {
    activeEditor.dispatchCommand(INSERT_IMAGE_COMMAND, payload);
    onClose();
  };

  return (
    <>
      {!mode && (
        <Box sx={{ display: 'flex', flexDirection: 'column', width: '100%' }}>
          <Button
            data-testid="image-modal-option-url"
            role="button"
            variant="outlined"
            disableRipple
            size="medium"
            color="primary"
            onClick={() => setMode('url')}
            sx={{ minWidth: 100, mb: 2 }}
          >
            {i18n.formatMessage({ id: 'rich_text_editor_external_image' })}
          </Button>
          <Button
            data-testid="image-modal-option-file"
            role="button"
            variant="outlined"
            disableRipple
            size="medium"
            color="primary"
            onClick={() => setMode('file')}
            sx={{ minWidth: 100 }}
          >
            {i18n.formatMessage({ id: 'upload' })}
          </Button>
        </Box>
      )}
      {mode === 'url' && <InsertImageUriDialogBody onClick={onClick} />}
      {mode === 'file' && <InsertImageUploadedDialogBody onClick={onClick} />}
    </>
  );
}

export default function ImagesPlugin({
  captionsEnabled
}: {
  captionsEnabled?: boolean;
}): JSX.Element | null {
  const [editor] = useLexicalComposerContext();

  useEffect(() => {
    if (!editor.hasNodes([ImageNode])) {
      throw new Error('ImagesPlugin: ImageNode not registered on editor');
    }

    return mergeRegister(
      editor.registerCommand<InsertImagePayload>(
        INSERT_IMAGE_COMMAND,
        payload => {
          const imageNode = $createImageNode(payload);
          $insertNodes([imageNode]);

          if ($isRootOrShadowRoot(imageNode.getParentOrThrow())) {
            $wrapNodeInElement(imageNode, $createParagraphNode).selectEnd();
          }

          return true;
        },
        COMMAND_PRIORITY_EDITOR
      ),
      editor.registerCommand<DragEvent>(
        DRAGSTART_COMMAND,
        event => {
          return onDragStart(event);
        },
        COMMAND_PRIORITY_HIGH
      ),
      editor.registerCommand<DragEvent>(
        DRAGOVER_COMMAND,
        event => {
          return onDragover(event);
        },
        COMMAND_PRIORITY_LOW
      ),
      editor.registerCommand<DragEvent>(
        DROP_COMMAND,
        event => {
          return onDrop(event, editor);
        },
        COMMAND_PRIORITY_HIGH
      )
    );
  }, [captionsEnabled, editor]);

  return null;
}

const TRANSPARENT_IMAGE =
  'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
const img = document.createElement('img');
img.src = TRANSPARENT_IMAGE;

function onDragStart(event: DragEvent): boolean {
  const node = getImageNodeInSelection();

  if (!node) {
    return false;
  }

  const dataTransfer = event.dataTransfer;

  if (!dataTransfer) {
    return false;
  }

  dataTransfer.setData('text/plain', '_');
  dataTransfer.setDragImage(img, 0, 0);
  dataTransfer.setData(
    'application/x-lexical-drag',
    JSON.stringify({
      data: {
        altText: node.__altText,
        caption: node.__caption,
        height: node.__height,
        key: node.getKey(),
        maxWidth: node.__maxWidth,
        showCaption: node.__showCaption,
        src: node.__src,
        width: node.__width
      },
      type: 'image'
    })
  );

  return true;
}

function onDragover(event: DragEvent): boolean {
  const node = getImageNodeInSelection();

  if (!node) {
    return false;
  }

  if (!canDropImage(event)) {
    event.preventDefault();
  }

  return true;
}

function onDrop(event: DragEvent, editor: LexicalEditor): boolean {
  const node = getImageNodeInSelection();

  if (!node) {
    return false;
  }

  const data = getDragImageData(event);

  if (!data) {
    return false;
  }

  event.preventDefault();

  if (canDropImage(event)) {
    const range = getDragSelection(event);
    node.remove();
    const rangeSelection = $createRangeSelection();

    if (range !== null && range !== undefined) {
      rangeSelection.applyDOMRange(range);
    }

    $setSelection(rangeSelection);
    editor.dispatchCommand(INSERT_IMAGE_COMMAND, data);
  }

  return true;
}

function getImageNodeInSelection(): ImageNode | null {
  const selection = $getSelection();

  if (!$isNodeSelection(selection)) {
    return null;
  }

  const nodes = selection.getNodes();
  const node = nodes[0];

  return $isImageNode(node) ? node : null;
}

function getDragImageData(event: DragEvent): null | InsertImagePayload {
  const dragData = event.dataTransfer?.getData('application/x-lexical-drag');

  if (!dragData) {
    return null;
  }

  const { type, data } = JSON.parse(dragData);

  if (type !== 'image') {
    return null;
  }

  return data;
}

declare global {
  interface DragEvent {
    rangeOffset?: number;
    rangeParent?: Node;
  }
}

function canDropImage(event: DragEvent): boolean {
  const target = event.target;

  return !!(
    target &&
    target instanceof HTMLElement &&
    !target.closest('code, span.editor-image') &&
    target.parentElement &&
    target.parentElement.closest('div.ContentEditable__root')
  );
}

function getDragSelection(event: DragEvent): Range | null | undefined {
  let range;
  const target = event.target as null | Element | Document;
  const targetWindow =
    target == null
      ? null
      : target.nodeType === 9
      ? (target as Document).defaultView
      : (target as Element).ownerDocument.defaultView;
  const domSelection = getDOMSelection(targetWindow);

  if (document.caretRangeFromPoint) {
    range = document.caretRangeFromPoint(event.clientX, event.clientY);
  } else if (event.rangeParent && domSelection !== null) {
    domSelection.collapse(event.rangeParent, event.rangeOffset || 0);
    range = domSelection.getRangeAt(0);
  } else {
    throw Error('Cannot get the selection when dragging');
  }

  return range;
}

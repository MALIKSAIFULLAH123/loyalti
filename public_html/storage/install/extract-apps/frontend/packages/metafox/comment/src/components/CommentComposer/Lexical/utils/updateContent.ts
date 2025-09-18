import { $getRoot, $createParagraphNode, $insertNodes } from 'lexical';
import { $generateNodesFromDOM } from '@lexical/html';

const updateContent = (editor, value) => {
  const root = $getRoot();
  root.clear();

  if (!value) {
    const paragraph = $createParagraphNode();
    root.append(paragraph);

    return;
  }

  const parser = new DOMParser();
  const dom = parser.parseFromString(
    (value || '').replace(/\n/g, '<br>'),
    'text/html'
  );
  const nodes = $generateNodesFromDOM(editor, dom);
  // Select the root
  $getRoot().select();

  // Insert them at a selection.
  $insertNodes(nodes);
};

export default updateContent;

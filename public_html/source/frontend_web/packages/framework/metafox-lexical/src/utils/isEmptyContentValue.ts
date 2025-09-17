import {
  $getRoot,
  $isDecoratorNode,
  $isElementNode,
  $isParagraphNode,
  $isTextNode
} from 'lexical';
import { $isRootTextContentEmpty } from '@lexical/text';

function isEmptyContentValue(isComposing: boolean, trim = true): boolean {
  if (!$isRootTextContentEmpty(isComposing, trim)) {
    return false;
  }

  const root = $getRoot();
  const children = root.getChildren();
  const childrenLength = children.length;

  if (childrenLength > 1) {
    return false;
  }

  for (let i = 0; i < childrenLength; i++) {
    const topBlock = children[i];

    if ($isDecoratorNode(topBlock)) {
      return false;
    }

    if ($isElementNode(topBlock)) {
      if (!$isParagraphNode(topBlock)) {
        return false;
      }

      if (topBlock.__indent !== 0) {
        return false;
      }

      const topBlockChildren = topBlock.getChildren();
      const topBlockChildrenLength = topBlockChildren.length;

      for (let s = 0; s < topBlockChildrenLength; s++) {
        const child = topBlockChildren[i];

        if (!$isTextNode(child)) {
          return false;
        }
      }
    }
  }

  return true;
}

export default isEmptyContentValue;

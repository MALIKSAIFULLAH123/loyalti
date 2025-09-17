import {
  INSERT_CHECK_LIST_COMMAND,
  INSERT_ORDERED_LIST_COMMAND,
  INSERT_UNORDERED_LIST_COMMAND
} from '@lexical/list';
import { $createHeadingNode, HeadingTagType } from '@lexical/rich-text';
import { $setBlocksType } from '@lexical/selection';
import {
  $createParagraphNode,
  $getSelection,
  LexicalEditor,
  $isRangeSelection
} from 'lexical';
import * as React from 'react';
import { DropDown, DropDownItem } from '@metafox/lexical/ui';
import TextParagraphIcon from '@metafox/lexical/assets/images/icons/text-paragraph.svg';
import TextH1Icon from '@metafox/lexical/assets/images/icons/type-h1.svg';
import TextH2Icon from '@metafox/lexical/assets/images/icons/type-h2.svg';
import TextH3Icon from '@metafox/lexical/assets/images/icons/type-h3.svg';
import TextBulletListIcon from '@metafox/lexical/assets/images/icons/list-ul.svg';
import TextNumberListIcon from '@metafox/lexical/assets/images/icons/list-ol.svg';
import TextCheckListIcon from '@metafox/lexical/assets/images/icons/square-check.svg';
import DropDownInnerItem from './DropDownInnerItem';
import { useGlobal } from '@metafox/framework';

const blockTypeToBlockConfig = {
  bullet: {
    label: 'rich_text_editor_bullet_list',
    icon: <TextBulletListIcon />
  },
  check: { label: 'rich_text_editor_check_list', icon: <TextCheckListIcon /> },
  h1: { label: 'rich_text_editor_heading_1', icon: <TextH1Icon /> },
  h2: { label: 'rich_text_editor_heading_2', icon: <TextH2Icon /> },
  h3: { label: 'rich_text_editor_heading_3', icon: <TextH3Icon /> },
  number: {
    label: 'rich_text_editor_numbered_list',
    icon: <TextNumberListIcon />
  },
  paragraph: { label: 'rich_text_editor_normal', icon: <TextParagraphIcon /> }
};

const rootTypeToRootName = {
  root: 'Root',
  table: 'Table'
};

function BlockFormatDropDown({
  editor,
  blockType,
  rootType,
  disabled = false
}: {
  blockType: keyof typeof blockTypeToBlockConfig;
  rootType: keyof typeof rootTypeToRootName;
  editor: LexicalEditor;
  disabled?: boolean;
}): JSX.Element {
  const { i18n } = useGlobal();

  const formatParagraph = () => {
    editor.update(() => {
      const selection = $getSelection();

      if ($isRangeSelection(selection)) {
        $setBlocksType(selection, () => $createParagraphNode());
      }
    });
  };

  const formatHeading = (headingSize: HeadingTagType) => {
    if (blockType !== headingSize) {
      editor.update(() => {
        const selection = $getSelection();

        if (selection !== null) {
          $setBlocksType(selection, () => $createHeadingNode(headingSize));
        }
      });
    }
  };

  const formatBulletList = () => {
    if (blockType !== 'bullet') {
      editor.dispatchCommand(INSERT_UNORDERED_LIST_COMMAND, undefined);
    } else {
      formatParagraph();
    }
  };

  const formatCheckList = () => {
    if (blockType !== 'check') {
      editor.dispatchCommand(INSERT_CHECK_LIST_COMMAND, undefined);
    } else {
      formatParagraph();
    }
  };

  const formatNumberedList = () => {
    if (blockType !== 'number') {
      editor.dispatchCommand(INSERT_ORDERED_LIST_COMMAND, undefined);
    } else {
      formatParagraph();
    }
  };

  return (
    <DropDown
      disabled={disabled}
      buttonLabel={i18n.formatMessage({
        id: blockTypeToBlockConfig[blockType]?.label || 'Block Format'
      })}
      buttonIcon={blockTypeToBlockConfig[blockType]?.icon}
      buttonAriaLabel="Formatting options for text style"
    >
      <DropDownItem
        className={'item'}
        onClick={formatParagraph}
        selected={blockType === 'paragraph'}
      >
        <DropDownInnerItem
          icon={blockTypeToBlockConfig['paragraph']['icon']}
          label={i18n.formatMessage({
            id: blockTypeToBlockConfig['paragraph']['label']
          })}
        />
      </DropDownItem>
      <DropDownItem
        className={'item '}
        onClick={() => formatHeading('h1')}
        selected={blockType === 'h1'}
      >
        <DropDownInnerItem
          icon={blockTypeToBlockConfig['h1']['icon']}
          label={i18n.formatMessage({
            id: blockTypeToBlockConfig['h1']['label']
          })}
        />
      </DropDownItem>
      <DropDownItem
        className={'item '}
        onClick={() => formatHeading('h2')}
        selected={blockType === 'h2'}
      >
        <DropDownInnerItem
          icon={blockTypeToBlockConfig['h2']['icon']}
          label={i18n.formatMessage({
            id: blockTypeToBlockConfig['h2']['label']
          })}
        />
      </DropDownItem>
      <DropDownItem
        className={'item'}
        onClick={() => formatHeading('h3')}
        selected={blockType === 'h3'}
      >
        <DropDownInnerItem
          icon={blockTypeToBlockConfig['h3']['icon']}
          label={i18n.formatMessage({
            id: blockTypeToBlockConfig['h3']['label']
          })}
        />
      </DropDownItem>
      <DropDownItem
        className={'item '}
        onClick={formatBulletList}
        selected={blockType === 'bullet'}
      >
        <DropDownInnerItem
          icon={blockTypeToBlockConfig['bullet']['icon']}
          label={i18n.formatMessage({
            id: blockTypeToBlockConfig['bullet']['label']
          })}
        />
      </DropDownItem>
      <DropDownItem
        className={'item '}
        onClick={formatNumberedList}
        selected={blockType === 'number'}
      >
        <DropDownInnerItem
          icon={blockTypeToBlockConfig['number']['icon']}
          label={i18n.formatMessage({
            id: blockTypeToBlockConfig['number']['label']
          })}
        />
      </DropDownItem>
      <DropDownItem
        className={'item'}
        onClick={formatCheckList}
        selected={blockType === 'check'}
      >
        <DropDownInnerItem
          icon={blockTypeToBlockConfig['check']['icon']}
          label={i18n.formatMessage({
            id: blockTypeToBlockConfig['check']['label']
          })}
        />
      </DropDownItem>
    </DropDown>
  );
}

export default BlockFormatDropDown;

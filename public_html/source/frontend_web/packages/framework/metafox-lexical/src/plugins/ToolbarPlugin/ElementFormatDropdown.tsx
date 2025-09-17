import {
  ElementFormatType,
  FORMAT_ELEMENT_COMMAND,
  LexicalEditor
} from 'lexical';
import React, { ReactNode } from 'react';
import { DropDown, DropDownItem } from '@metafox/lexical/ui';
import LeftAlignIcon from '@metafox/lexical/assets/images/icons/text-left.svg';
import RightAlignIcon from '@metafox/lexical/assets/images/icons/text-right.svg';
import CenterAlignIcon from '@metafox/lexical/assets/images/icons/text-center.svg';
import JustifyAlignIcon from '@metafox/lexical/assets/images/icons/justify.svg';
import DropdownInnerItem from './DropDownInnerItem';
import { useGlobal } from '@metafox/framework';

const ELEMENT_FORMAT_OPTIONS: {
  [key in Exclude<ElementFormatType, ''>]: {
    icon: ReactNode;
    iconRTL: ReactNode;
    label: string;
    command: string;
  };
} = {
  center: {
    icon: <CenterAlignIcon />,
    iconRTL: <CenterAlignIcon />,
    label: 'rich_text_editor_center_align',
    command: 'center'
  },
  left: {
    icon: <LeftAlignIcon />,
    iconRTL: <LeftAlignIcon />,
    label: 'rich_text_editor_left_align',
    command: 'left'
  },
  right: {
    icon: <RightAlignIcon />,
    iconRTL: <RightAlignIcon />,
    label: 'rich_text_editor_right_align',
    command: 'right'
  },
  start: {
    icon: <LeftAlignIcon />,
    iconRTL: <LeftAlignIcon />,
    label: 'rich_text_editor_start_align',
    command: 'start'
  },
  justify: {
    icon: <JustifyAlignIcon />,
    iconRTL: <JustifyAlignIcon />,
    label: 'rich_text_editor_justify_align',
    command: 'justify'
  },
  end: {
    icon: <RightAlignIcon />,
    iconRTL: <RightAlignIcon />,
    label: 'rich_text_editor_end_align',
    command: 'end'
  }
};

function ElementFormatDropdown({
  editor,
  value,
  isRTL,
  disabled = false
}: {
  editor: LexicalEditor;
  value: ElementFormatType;
  isRTL: boolean;
  disabled: boolean;
}) {
  const { i18n } = useGlobal();
  const formatOptionCurrent = ELEMENT_FORMAT_OPTIONS[value || 'left'];
  const formatOptions = Object.keys(ELEMENT_FORMAT_OPTIONS);

  return (
    <DropDown
      disabled={disabled}
      buttonLabel={i18n.formatMessage({
        id: formatOptionCurrent?.label || 'format'
      })}
      buttonIcon={
        isRTL ? formatOptionCurrent?.iconRTL : formatOptionCurrent?.icon
      }
      buttonAriaLabel={i18n.formatMessage({
        id: 'rich_text_editor_format_text_align_description'
      })}
    >
      {formatOptions.map(option => (
        <DropDownItem
          key={option}
          onClick={() => {
            editor.dispatchCommand(
              FORMAT_ELEMENT_COMMAND,
              ELEMENT_FORMAT_OPTIONS[option]['command']
            );
          }}
          className={'item '}
          selected={value === option}
        >
          <DropdownInnerItem
            icon={
              isRTL
                ? ELEMENT_FORMAT_OPTIONS[option]['iconRTL']
                : ELEMENT_FORMAT_OPTIONS[option]['icon']
            }
            label={
              ELEMENT_FORMAT_OPTIONS[option]['label']
                ? i18n.formatMessage({
                    id: ELEMENT_FORMAT_OPTIONS[option]['label']
                  })
                : ''
            }
          />
        </DropDownItem>
      ))}
    </DropDown>
  );
}

export default ElementFormatDropdown;

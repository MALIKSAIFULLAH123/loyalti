import * as React from 'react';
import {
  ReactNode,
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState
} from 'react';
import { createPortal } from 'react-dom';
import { LineIcon } from '@metafox/ui';
import { styled, Typography, MenuItem, Button } from '@mui/material';
import { DropdownWrapper } from './DropdownWrapperUI';
import { SxProps } from '@mui/system';
type DropDownContextType = {
  registerItem: (ref: React.RefObject<HTMLButtonElement>) => void;
};
const name = 'RichtextEditorAction';
const DropDownContext = React.createContext<DropDownContextType | null>(null);

const dropDownPadding = 4;

const ButtonAction = styled(Button, {
  name,
  slot: 'ButtonAction'
})(({ theme }) => ({
  border: 0,
  color: theme.palette.text.primary,
  display: 'flex',
  background: 'none',
  borderRadius: '8px',
  padding: '8px',
  cursor: 'pointer',
  verticalAlign: 'middle',
  alignItems: 'center',
  justifyContent: 'space-between',
  minWidth: '48px',
  '&:hover:not([disabled])': {
    backgroundColor: 'rgba(0,0,0,0.1)'
  }
}));

export function DropDownItem({
  children,
  className,
  onClick,
  title,
  selected
}: {
  children: React.ReactNode;
  className: string;
  onClick: (event: React.MouseEvent<HTMLButtonElement>) => void;
  title?: string;
  selected?: boolean;
}) {
  const ref = useRef<HTMLButtonElement>(null);

  const dropDownContext = React.useContext(DropDownContext);

  if (dropDownContext === null) {
    throw new Error('DropDownItem must be used within a DropDown');
  }

  const { registerItem } = dropDownContext;

  useEffect(() => {
    if (ref && ref.current) {
      registerItem(ref);
    }
  }, [ref, registerItem]);

  return (
    <MenuItem
      className={className}
      onClick={onClick}
      selected={selected}
      ref={ref}
      title={title}
    >
      {children}
    </MenuItem>
  );
}

function DropDownItems({
  children,
  dropDownRef,
  onClose,
  sxDropdownWrapper = {}
}: {
  children: React.ReactNode;
  dropDownRef: React.Ref<HTMLDivElement>;
  onClose: () => void;
  sxDropdownWrapper?: SxProps;
}) {
  const [items, setItems] = useState<React.RefObject<HTMLButtonElement>[]>();
  const [highlightedItem, setHighlightedItem] =
    useState<React.RefObject<HTMLButtonElement>>();

  const registerItem = useCallback(
    (itemRef: React.RefObject<HTMLButtonElement>) => {
      setItems(prev => (prev ? [...prev, itemRef] : [itemRef]));
    },
    [setItems]
  );

  const handleKeyDown = (event: React.KeyboardEvent<HTMLDivElement>) => {
    if (!items) return;

    const key = event.key;

    if (['Escape', 'ArrowUp', 'ArrowDown', 'Tab'].includes(key)) {
      event.preventDefault();
    }

    if (key === 'Escape' || key === 'Tab') {
      onClose();
    } else if (key === 'ArrowUp') {
      setHighlightedItem(prev => {
        if (!prev) return items[0];

        const index = items.indexOf(prev) - 1;

        return items[index === -1 ? items.length - 1 : index];
      });
    } else if (key === 'ArrowDown') {
      setHighlightedItem(prev => {
        if (!prev) return items[0];

        return items[items.indexOf(prev) + 1];
      });
    }
  };

  const contextValue = useMemo(
    () => ({
      registerItem
    }),
    [registerItem]
  );

  useEffect(() => {
    if (items && !highlightedItem) {
      setHighlightedItem(items[0]);
    }

    if (highlightedItem && highlightedItem.current) {
      highlightedItem.current.focus();
    }
  }, [items, highlightedItem]);

  return (
    <DropDownContext.Provider value={contextValue}>
      <DropdownWrapper
        className="dropdown"
        ref={dropDownRef}
        onKeyDown={handleKeyDown}
        sx={sxDropdownWrapper}
      >
        {children}
      </DropdownWrapper>
    </DropDownContext.Provider>
  );
}

export default function DropDown({
  disabled = false,
  buttonLabel,
  buttonAriaLabel,
  buttonIcon,
  children,
  stopCloseOnClickSelf,
  sxDropdownWrapper
}: {
  disabled?: boolean;
  buttonAriaLabel?: string;
  buttonLabel?: string;
  buttonIcon?: ReactNode;
  children: ReactNode;
  stopCloseOnClickSelf?: boolean;
  sxDropdownWrapper?: SxProps;
}): JSX.Element {
  const dropDownRef = useRef<HTMLDivElement>(null);
  const buttonRef = useRef<HTMLButtonElement>(null);
  const [showDropDown, setShowDropDown] = useState(false);

  const handleClose = () => {
    setShowDropDown(false);

    if (buttonRef && buttonRef.current) {
      buttonRef.current.focus();
    }
  };

  useEffect(() => {
    const button = buttonRef.current;
    const dropDown = dropDownRef.current;

    if (showDropDown && button !== null && dropDown !== null) {
      const { top, left } = button.getBoundingClientRect();
      dropDown.style.top = `${top + button.offsetHeight + dropDownPadding}px`;
      dropDown.style.left = `${Math.min(
        left,
        window.innerWidth - dropDown.offsetWidth - 20
      )}px`;
    }
  }, [dropDownRef, buttonRef, showDropDown]);

  useEffect(() => {
    const button = buttonRef.current;

    if (button !== null && showDropDown) {
      const handle = (event: MouseEvent) => {
        const target = event.target;

        if (stopCloseOnClickSelf) {
          if (
            dropDownRef.current &&
            dropDownRef.current.contains(target as Node)
          )
            return;
        }

        if (!button.contains(target as Node)) {
          setShowDropDown(false);
        }
      };
      document.addEventListener('click', handle);

      return () => {
        document.removeEventListener('click', handle);
      };
    }
  }, [dropDownRef, buttonRef, showDropDown, stopCloseOnClickSelf]);

  useEffect(() => {
    const handleButtonPositionUpdate = () => {
      if (showDropDown) {
        const button = buttonRef.current;
        const dropDown = dropDownRef.current;

        if (button !== null && dropDown !== null) {
          const { top } = button.getBoundingClientRect();
          const newPosition = top + button.offsetHeight + dropDownPadding;

          if (newPosition !== dropDown.getBoundingClientRect().top) {
            dropDown.style.top = `${newPosition}px`;
          }
        }
      }
    };

    document.addEventListener('scroll', handleButtonPositionUpdate);

    return () => {
      document.removeEventListener('scroll', handleButtonPositionUpdate);
    };
  }, [buttonRef, dropDownRef, showDropDown]);

  return (
    <>
      <ButtonAction
        type="button"
        disabled={disabled}
        aria-label={buttonAriaLabel || buttonLabel}
        onClick={() => setShowDropDown(!showDropDown)}
        ref={buttonRef}
        variant="text"
      >
        {buttonIcon ? buttonIcon : null}
        {buttonLabel && (
          <Typography variant="body2" component="span" ml={1}>
            {buttonLabel}
          </Typography>
        )}
        <LineIcon sx={{ ml: 1 }} icon="ico-caret-down" />
      </ButtonAction>

      {showDropDown &&
        createPortal(
          <DropDownItems
            dropDownRef={dropDownRef}
            onClose={handleClose}
            sxDropdownWrapper={sxDropdownWrapper}
          >
            {children}
          </DropDownItems>,
          document.body
        )}
    </>
  );
}

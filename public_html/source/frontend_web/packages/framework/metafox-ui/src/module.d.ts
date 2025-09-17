import '@metafox/framework/Manager';
import { TPopoverContext } from './PopoverContext';
import { ItemActionMenuProps, TotalCommentProps } from './types';

declare module '@metafox/framework/Manager' {
  interface Manager {
    usePopover?: () => TPopoverContext;
    useDataGridContext?: () => DataGridContextShape;
    ItemActionMenu?: React.FC<ItemActionMenuProps>;
    TotalComment?: React.FC<TotalCommentProps>;
  }
}

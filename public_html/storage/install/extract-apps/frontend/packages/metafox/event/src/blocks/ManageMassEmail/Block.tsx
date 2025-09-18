/**
 * @type: block
 * name: event.block.manage_mass_email
 */
import { createBlock } from '@metafox/framework';
import Base, {
  EditingFormProps
} from '@metafox/core/blocks/EditingForm';

export default createBlock<EditingFormProps>({
  extendBlock: Base,
  defaults: {
    appName: 'event',
    resourceName: 'event',
    formName: 'editItem',
    blockLayout: 'Main Form'
  }
});

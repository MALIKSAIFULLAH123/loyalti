/**
 * @type: dialog
 * name: layout.dialog.EditBlock
 * chunkName: layoutEditor
 */
import { BlockEditorConfig, useGlobal } from '@metafox/framework';
import { FormBuilder, FormSchemaShape } from '@metafox/form';
import { EditLayoutBlockEditorProps } from '@metafox/layout/types';
import { getDotProps, mergeObjectProps, randomId } from '@metafox/utils';
import { Dialog, useTheme } from '@mui/material';
import { assign, isFunction } from 'lodash';
import * as React from 'react';
import baseBlockConfig from '../baseBlockConfig';
import { createFeatures } from './createFeatures';

export default function EditBlock(props: EditLayoutBlockEditorProps) {
  const { blockId, blockName, blockOrigin, pageName, pageSize, extra } = props;
  const manager = useGlobal();
  const theme = useTheme();
  const { layoutBackend, jsxBackend, useDialog, i18n } = manager;
  const { closeDialog, dialogProps, disableBackdropClick } = useDialog();
  const BlockComponent: any = jsxBackend.get(blockName);
  const submited = React.useRef(false);

  const [editorConfig, setEditorConfig] = React.useState<BlockEditorConfig>(
    BlockComponent?.editorConfig
  );

  React.useEffect(() => {
    disableBackdropClick(true);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const blockConfig = React.useMemo(() => {
    if (blockId) {
      return layoutBackend.getBlockConfig(
        pageName,
        pageSize,
        blockId,
        blockOrigin
      );
    } else {
      return {};
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [blockId, pageName, pageSize]);

  if (!BlockComponent) {
    // eslint-disable-next-line no-console
    console.warn('there are no blockView');
  }

  const { initialValues, formSchema } = React.useMemo(() => {
    if (!editorConfig) return {};

    const BaseBlock = editorConfig.extendBlock
      ? jsxBackend.get(editorConfig.extendBlock)
      : {};

    const initialValues = mergeObjectProps(
      baseBlockConfig,
      BaseBlock?.defaults,
      editorConfig.defaults,
      blockConfig,
      null,
      editorConfig.overrides
    );

    const formSchema: FormSchemaShape = {
      component: 'Form',
      dialog: true,
      title: i18n.formatMessage({
        id: blockId ? 'edit_layout_block' : 'add_layout_block'
      }),
      elements: {
        content: {
          component: 'Container',
          elements: {}
        },
        footer: {
          component: 'FormFooter',
          elements: {}
        }
      }
    };

    const disabled = getDotProps(editorConfig.overrides).reduce((acc, x) => {
      acc[x] = true;

      return acc;
    }, {});

    // const features = getDotProps(initialValues);

    createFeatures('editBlock', formSchema, {
      manager,
      features: [],
      disabled,
      extra,
      theme,
      config: editorConfig
    });

    if (editorConfig.custom) {
      Object.keys(editorConfig.custom).forEach(key => {
        formSchema.elements.content.elements[`custom_${key}`] = assign(
          {},
          editorConfig.custom[key]
        );
      });
    }

    if (editorConfig.customValidation) {
      formSchema.validation = assign({}, editorConfig.customValidation);
    }

    if ('core.block.mainListing' === blockName) {
      delete formSchema.elements.content.elements.dataSource;
    } else if ('core.block.listview' !== editorConfig.extendBlock) {
      delete formSchema.elements.content.elements.itemView;
      delete formSchema.elements.content.elements.dataSource;
      delete formSchema.elements.content.elements.gridContainerProps;
      delete formSchema.elements.content.elements.gridItemProps;
      delete formSchema.elements.content.elements.pagingProps;
      delete formSchema.elements.content.elements.emptyPageProps;
      delete formSchema.elements.content.elements.gridProps;
      delete formSchema.elements.content.elements.gridLayout;
      delete formSchema.elements.content.elements.gridVariant;
      delete formSchema.elements.content.elements.itemProps;
      delete formSchema.elements.content.elements.pagingProps;
      delete formSchema.elements.content.elements.extraListProps;
      delete formSchema.elements.content.elements.headerActions;
    }

    formSchema.elements.footer.elements = blockId
      ? {
          submit: {
            name: 'submit',
            type: 'submit',
            component: 'Button',
            variant: 'contained',
            label: i18n.formatMessage({ id: 'save_changes' }),
            color: 'primary'
          },
          cancel: {
            component: 'Cancel',
            label: i18n.formatMessage({ id: 'cancel' }),
            color: 'primary',
            variant: 'outlined'
          }
        }
      : {
          submit: {
            name: 'submit',
            type: 'submit',
            component: 'Button',
            variant: 'contained',
            label: i18n.formatMessage({ id: 'add' }),
            color: 'primary'
          },
          cancel: {
            component: 'Cancel',
            label: i18n.formatMessage({ id: 'cancel' }),
            color: 'primary',
            variant: 'outlined'
          }
        };

    return {
      initialValues,
      formSchema,
      disabled
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [editorConfig, jsxBackend]);

  const onSubmit = (values, form) => {
    if (submited.current) return;

    submited.current = true;
    let id = blockId;
    let blockData = {};

    if (!id) {
      // add new
      id = randomId();
      blockData = layoutBackend.addBlock({ ...props, blockId: id });
    }

    // update block configure
    updateBlockConfig({ ...props, ...blockData }, values);

    form.setSubmitting(false);
    closeDialog();
    layoutBackend.reload();
  };

  const updateBlockConfig = (
    { pageName, pageSize, blockId, blockOrigin, blockName, slotName },
    values
  ) => {
    layoutBackend.updateBlockConfig(
      pageName,
      pageSize,
      blockId,
      blockOrigin,
      prev => {
        if (blockName) {
          prev.component = blockName;
        }

        if (slotName) {
          prev.slotName = slotName;
        }

        // remove some data.
        Object.assign(prev, values);

        return prev;
      }
    );
  };

  if (!editorConfig && isFunction(BlockComponent?.load)) {
    BlockComponent.load().then(mod => {
      if (mod.default.editorConfig) {
        setEditorConfig(mod.default.editorConfig);
      }
    });
  }

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      {formSchema ? (
        <FormBuilder
          dialog
          initialValues={initialValues}
          formSchema={formSchema}
          onSubmit={onSubmit}
        />
      ) : null}
    </Dialog>
  );
}

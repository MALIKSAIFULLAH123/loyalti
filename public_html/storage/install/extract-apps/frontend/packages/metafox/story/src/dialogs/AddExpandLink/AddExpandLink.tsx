/**
 * @type: dialog
 * name: story.dialog.addExpandLink
 */

import { useGlobal, useResourceForm } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import React from 'react';
import { APP_STORY, RESOURCE_STORY } from '@metafox/story/constants';
import { SmartFormBuilder } from '@metafox/form';

type AddLinkProps = {
  updateItem: (data: any) => void;
  item?: any;
  nameField?: string;
};

export default function RemoteForm({
  updateItem,
  item,
  nameField
}: AddLinkProps) {
  const { useDialog } = useGlobal();

  const formSchema = useResourceForm(
    APP_STORY,
    RESOURCE_STORY,
    'expand_link_mobile'
  );
  const dialogItem = useDialog();
  const { dialogProps, closeDialog } = dialogItem;

  const initValues = nameField ? { [nameField]: item[nameField] } : {};

  const onSubmit = values => {
    updateItem(values);
    closeDialog();
  };

  return (
    <Dialog
      {...dialogProps}
      data-testid="addExpendLink"
      fullScreen={false}
      maxWidth="xs"
    >
      <SmartFormBuilder
        initialValues={initValues}
        dialog
        dialogItem={dialogItem}
        formSchema={formSchema}
        onSubmit={onSubmit}
      />
    </Dialog>
  );
}

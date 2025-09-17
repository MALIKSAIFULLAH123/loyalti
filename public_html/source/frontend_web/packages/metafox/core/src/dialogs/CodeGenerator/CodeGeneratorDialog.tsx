/**
 * @type: dialog
 * name: CodeGeneratorDialog
 */

import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { useGlobal, useLocation } from '@metafox/framework';
import Button from '@mui/material/Button';
import React from 'react';

export default function CodeGeneratorDialog() {
  const { useDialog } = useGlobal();
  const { pathname } = useLocation();

  const { dialogProps, setDialogValue } = useDialog();

  const MakeButton = ({
    payload,
    type = '@core/codeGenerate/make',
    children
  }) => {
    return (
      <Button
        onClick={() =>
          setDialogValue({
            type,
            payload
          })
        }
      >
        {children}
      </Button>
    );
  };

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <DialogTitle>Code Generator</DialogTitle>
      <DialogContent>
        <MakeButton payload="package">New App</MakeButton>
        <MakeButton payload="migration">Migration</MakeButton>
        <MakeButton payload="model">Model</MakeButton>
        <MakeButton payload="web-api">Web API</MakeButton>
        <MakeButton payload="admin-api">Admin API</MakeButton>
        <MakeButton payload="form">Forms</MakeButton>
        <MakeButton payload="request">Request</MakeButton>
        <MakeButton payload="category">Category</MakeButton>
        <MakeButton payload="data-grid">DataGrid</MakeButton>
        <MakeButton payload="listener">Listener</MakeButton>
        <MakeButton payload="notification">Notification</MakeButton>
        <MakeButton payload="job">Job</MakeButton>
        <MakeButton payload="mail">Mail</MakeButton>
        <MakeButton payload="export">Export</MakeButton>
        <MakeButton payload="inspect">Inspect</MakeButton>
        <MakeButton payload="importer">Importer</MakeButton>
        <Button
          onClick={() =>
            setDialogValue({
              type: '@core/codeGenerate/editCurrentSeo',
              payload: { url: pathname.replace(/^\/|\/$/g, '') }
            })
          }
        >
          Edit Meta
        </Button>
        <Button
          onClick={() =>
            setDialogValue({
              type: '@core/codeGenerate/ideFix'
            })
          }
        >
          IDE Fix
        </Button>
      </DialogContent>
    </Dialog>
  );
}

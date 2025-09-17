import { FormBuilderProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import RedoIcon from '@mui/icons-material/RedoOutlined';
import ResetIcon from '@mui/icons-material/RestartAltOutlined';
import UndoIcon from '@mui/icons-material/UndoOutlined';
import SaveIcon from '@mui/icons-material/Save';
import { Box, Button, IconButton, Tooltip } from '@mui/material';
import React from 'react';
import { Content, Footer } from './LayoutEditor';

type ComponentProps = Pick<
  FormBuilderProps,
  'onSubmit' | 'initialValues' | 'name'
>;

export default function StyleEditor({
  component: Component
}: {
  component: React.FC<ComponentProps>;
}) {
  // don't useTheme() because of context.
  //  here because of normalization data.
  const { layoutBackend, formRefs, theme, i18n } = useGlobal();
  const formName = 'theme.style.editor';
  const { mode } = theme.palette;

  const initialValues = React.useMemo(() => {
    return layoutBackend.getThemeConfigForParticularMode(mode);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onSubmit = React.useCallback(
    (values, form) => {
      const data = layoutBackend.getVariant();
      const key = mode === 'dark' ? 'dark' : 'default';
      data[key] = values;
      layoutBackend.setVariant(data);
      layoutBackend.setVariantDirty(true);
      form.setSubmitting(false);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [mode]
  );

  const handlePreview = React.useCallback(() => {
    formRefs.get(formName)?.submitForm();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleReset = React.useCallback(() => {
    formRefs.get(formName)?.resetForm();
    formRefs.get(formName)?.submitForm();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [mode]);

  const handleSave = React.useCallback(() => {
    formRefs.get(formName)?.submitForm();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <>
      <Content>
        <Component
          name={formName}
          onSubmit={onSubmit}
          initialValues={initialValues}
        />
      </Content>
      <Footer>
        <Button
          variant="outlined"
          size="small"
          onClick={handlePreview}
          sx={{ display: 'none' }}
        >
          {i18n.formatMessage({ id: 'preview' })}
        </Button>
        <Box sx={{ flex: 1 }} />
        <IconButton size="small" onClick={handleSave} sx={{ display: 'none' }}>
          <Tooltip title={i18n.formatMessage({ id: 'save' })}>
            <SaveIcon />
          </Tooltip>
        </IconButton>
        <IconButton size="small" onClick={handleReset} sx={{ display: 'none' }}>
          <Tooltip title={i18n.formatMessage({ id: 'undo' })}>
            <UndoIcon />
          </Tooltip>
        </IconButton>
        <IconButton size="small" onClick={handleReset} sx={{ display: 'none' }}>
          <Tooltip title={i18n.formatMessage({ id: 'redo' })}>
            <RedoIcon />
          </Tooltip>
        </IconButton>
        <IconButton size="small" onClick={handleReset}>
          <Tooltip title={i18n.formatMessage({ id: 'reset' })}>
            <ResetIcon />
          </Tooltip>
        </IconButton>
      </Footer>
    </>
  );
}

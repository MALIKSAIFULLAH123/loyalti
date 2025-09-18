import { SmartFormBuilder } from '@metafox/form';
import { useGlobal, useResourceAction } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import {
  APP_STORY,
  RESOURCE_STORY,
  STATUS_PHOTO_STORY,
  STATUS_TEXT_STORY
} from '@metafox/story/constants';
import useAddFormContext from '@metafox/story/hooks';
import { LineIcon } from '@metafox/ui';
import {
  Box,
  Collapse,
  IconButton,
  Paper,
  Typography,
  styled
} from '@mui/material';
import { isFunction } from 'lodash';
import React from 'react';

const name = 'StoryFormViewDetail';

const WrapperBox = styled(Box)(({ theme }) => ({
  position: 'absolute',
  bottom: 0,
  right: 0,
  width: '100%',
  height: '100%',
  zIndex: 98
}));

const FormViewDetail = styled(Paper, {
  name,
  shouldForwardProp: prop => prop !== 'isMinHeight'
})<{ isMinHeight?: boolean }>(({ theme, isMinHeight }) => ({
  position: 'absolute',
  bottom: 0,
  width: '100%',
  height: isMinHeight ? '85%' : '50%',
  backgroundColor: theme.palette.background.paper,
  padding: theme.spacing(2),
  paddingTop: theme.spacing(2.5),
  display: 'flex',
  flexDirection: 'column',
  zIndex: 99,
  borderRadius: 0,
  borderTopLeftRadius: theme.shape.borderRadius,
  borderTopRightRadius: theme.shape.borderRadius
}));

const HeaderBlock = styled(Box, { name, slot: 'HeaderBlock' })(({ theme }) => ({
  padding: theme.spacing(2),
  paddingTop: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));
const HeaderTitle = styled(Box, { name, slot: 'HeaderTitle' })(() => ({}));
const ContentBlock = styled(Box, { name, slot: 'ContentBlock' })(
  ({ theme }) => ({
    borderTop: theme.mixins.border('secondary'),
    overflow: 'hidden'
  })
);

const CloseButton = styled(IconButton, { name })(() => ({
  marginRight: 'auto',
  transform: 'translate(4px,0)',
  position: 'absolute',
  right: '16px'
}));

const FormWrapper = styled('div')(({ theme }) => ({
  height: '100%',
  '& > form': {
    height: '100%',
    flexWrap: 'nowrap',
    display: 'flex',
    flexDirection: 'column'
  }
}));

interface Props {
  open: boolean;
  isMinHeight?: boolean;
  setOpen: any;
  setHiddenActionText?: any;
}

const FormStoryMobile = ({
  open,
  setOpen,
  setHiddenActionText = value => {},
  isMinHeight = false
}: Props) => {
  const { useContentParams, usePageParams, i18n } = useGlobal();
  const context = useAddFormContext();

  const handleClose = () => {
    setOpen(false);
    isFunction(setHiddenActionText) && setHiddenActionText(false);
  };

  const { mainForm } = useContentParams();
  const pageParams = usePageParams();

  let actionName = '';

  if (context?.status === STATUS_PHOTO_STORY) {
    actionName = 'addPhotoStoryMobile';
  }

  if (context?.status === STATUS_TEXT_STORY) {
    actionName = 'addTextStoryMobile';
  }

  const dataSource = useResourceAction(APP_STORY, RESOURCE_STORY, actionName);

  if (!mainForm || !dataSource) return null;

  const {
    noHeader,
    noBreadcrumb,
    formSchema,
    breadcrumbs,
    disableFormOnSuccess
  } = mainForm;

  return (
    <Collapse in={open} orientation="vertical">
      <WrapperBox>
        <FormViewDetail
          data-testid="content-viewDetail"
          isMinHeight={isMinHeight}
        >
          <HeaderBlock>
            <HeaderTitle>
              <Typography variant="h4" color={'text.primary'}>
                {i18n.formatMessage({ id: 'privacy_settings' })}
              </Typography>
            </HeaderTitle>
            <CloseButton
              size="small"
              onClick={handleClose}
              data-testid="buttonClose"
              role="button"
            >
              <LineIcon icon="ico-close" />
            </CloseButton>
          </HeaderBlock>
          <ContentBlock>
            <ScrollContainer autoHide autoHeight autoHeightMax={'100%'}>
              <FormWrapper>
                <SmartFormBuilder
                  noHeader={noHeader}
                  breadcrumbs={breadcrumbs}
                  noBreadcrumb={noBreadcrumb}
                  formSchema={formSchema}
                  pageParams={pageParams}
                  dataSource={dataSource}
                  initialValues={null}
                  disableFormOnSuccess={disableFormOnSuccess}
                  changeEventName={mainForm.changeEventName}
                  navigationConfirmWhenDirty={false}
                  submitAction="story/submitFormAdd"
                />
              </FormWrapper>
            </ScrollContainer>
          </ContentBlock>
        </FormViewDetail>
      </WrapperBox>
    </Collapse>
  );
};

export default React.memo(FormStoryMobile);

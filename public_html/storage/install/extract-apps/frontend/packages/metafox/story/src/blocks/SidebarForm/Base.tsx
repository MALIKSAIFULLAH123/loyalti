import { SmartFormBuilder } from '@metafox/form';
import { RouteLink, useGlobal, useResourceAction } from '@metafox/framework';
import { BlockContent, Block, ScrollContainer } from '@metafox/layout';
import {
  APP_STORY,
  RESOURCE_STORY,
  STATUS_PHOTO_STORY,
  STATUS_TEXT_STORY
} from '@metafox/story/constants';
import { Typography, styled } from '@mui/material';
import React from 'react';

const FormWrapper = styled('div')(({ theme }) => ({
  height: '100%',
  '& > form': {
    height: '100%',
    flexWrap: 'nowrap',
    display: 'flex',
    flexDirection: 'column'
  }
}));

const BackLinkProps = styled(RouteLink)(({ theme }) => ({
  display: 'inline-block',
  color: theme.palette.primary.main,
  '&:hover': {
    textDecoration: 'underline'
  }
}));

function Base({ title = 'create_story', initialValues, status, backProps }) {
  const {
    useContentParams,
    usePageParams,
    i18n,
    jsxBackend,
    setNavigationConfirm
  } = useGlobal();

  const { mainForm } = useContentParams();
  const pageParams = usePageParams();

  const dataSourcePhoto = useResourceAction(
    APP_STORY,
    RESOURCE_STORY,
    'addPhotoStory'
  );

  const dataSourceText = useResourceAction(
    APP_STORY,
    RESOURCE_STORY,
    'addTextStory'
  );

  const confirmInfo = {
    message: i18n.formatMessage({
      id: 'if_you_leave_form_no_save_changed'
    }),
    title: i18n.formatMessage({
      id: 'are_you_sure'
    }),
    negativeButton: {
      label: i18n.formatMessage({
        id: 'cancel'
      })
    },
    positiveButton: {
      label: i18n.formatMessage({
        id: 'ok'
      })
    }
  };

  React.useEffect(() => {
    if (status === STATUS_PHOTO_STORY) {
      setNavigationConfirm(true, confirmInfo);
    }

    return () => {
      if (status === STATUS_PHOTO_STORY) {
        setNavigationConfirm && setNavigationConfirm(false);
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [status]);

  const SideBarHeader = jsxBackend.get('core.sideAppHeaderBlock');

  if (!mainForm?.dataSource && !mainForm.formSchema) {
    return null;
  }

  const {
    noHeader,
    noBreadcrumb,
    formSchema,
    breadcrumbs,
    minHeight,
    disableFormOnSuccess
  } = mainForm;

  if (status === STATUS_PHOTO_STORY) {
    return (
      <ScrollContainer autoHide autoHeight autoHeightMax={'100%'}>
        <Block testid="mainForm">
          {backProps ? (
            <BackLinkProps to={backProps.to}>
              <Typography variant="body2" color="primary">
                {i18n.formatMessage({ id: backProps.title })}
              </Typography>
            </BackLinkProps>
          ) : null}
          <BlockContent style={{ minHeight }}>
            <FormWrapper>
              <SmartFormBuilder
                noHeader={noHeader}
                breadcrumbs={breadcrumbs}
                noBreadcrumb={noBreadcrumb}
                formSchema={formSchema}
                pageParams={pageParams}
                dataSource={dataSourcePhoto}
                initialValues={initialValues}
                disableFormOnSuccess={disableFormOnSuccess}
                changeEventName={mainForm.changeEventName}
                navigationConfirmWhenDirty={false}
                submitAction="story/submitFormAdd"
              />
            </FormWrapper>
          </BlockContent>
        </Block>
      </ScrollContainer>
    );
  }

  if (status === STATUS_TEXT_STORY) {
    return (
      <ScrollContainer autoHide autoHeight autoHeightMax={'100%'}>
        <Block testid="mainForm">
          {backProps ? (
            <BackLinkProps to={backProps.to}>
              <Typography variant="body2" color="primary">
                {i18n.formatMessage({ id: backProps.title })}
              </Typography>
            </BackLinkProps>
          ) : null}
          <BlockContent style={{ minHeight }}>
            <FormWrapper>
              <SmartFormBuilder
                noHeader={noHeader}
                breadcrumbs={breadcrumbs}
                noBreadcrumb={noBreadcrumb}
                formSchema={formSchema}
                pageParams={pageParams}
                dataSource={dataSourceText}
                initialValues={initialValues}
                disableFormOnSuccess={disableFormOnSuccess}
                changeEventName={mainForm.changeEventName}
                navigationConfirmWhenDirt={false}
                submitAction="story/submitFormAdd"
              />
            </FormWrapper>
          </BlockContent>
        </Block>
      </ScrollContainer>
    );
  }

  return <SideBarHeader />;
}

export default Base;

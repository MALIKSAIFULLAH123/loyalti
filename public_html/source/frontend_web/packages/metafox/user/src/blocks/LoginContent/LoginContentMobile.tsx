/**
 * @type: block
 * name: user.block.userLoginContentMobile
 * title: User Login Block Mobile
 * keywords: general
 * chunkName: boot
 */
import {
  BlockViewProps,
  createBlock,
  useGlobal,
  useSession
} from '@metafox/framework';
import { Container } from '@metafox/ui';
import { Box, Grid, Typography, styled } from '@mui/material';
import React from 'react';
import LoginForm from './LoginForm';
import LoginLanguages from './LoginLanguages';
import LogoBranch from './LogoBranch';

const name = 'LoginMobile';

const Root = styled(Box, {
  name,
  slot: 'RootLogin',
  overridesResolver(props, styles) {
    return [styles.rootLogin];
  }
})(({ theme }) => ({
  minHeight: '100vh',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center'
}));

const ContainerWrapper = styled(Container, {
  name
})(({ theme }) => ({
  display: 'flex',
  flexFlow: 'column',
  justifyContent: 'center',
  maxWidth: '1074px',
  paddingTop: theme.spacing(2.5),
  paddingLeft: theme.spacing(1.5),
  paddingRight: theme.spacing(1.5)
}));

const ContainerGrid = styled(Grid, {
  name,
  shouldForwardProp: prop => prop !== 'multipleAccess',
  slot: 'ContainerGridLogin',
  overridesResolver(props, styles) {
    return [styles.containerGridLogin];
  }
})<{ multipleAccess?: boolean }>(({ theme, multipleAccess }) => ({
  backgroundColor: theme.palette.background.paper,
  marginBottom: theme.spacing(3),
  borderRadius: theme.shape.borderRadius,
  backgroundPosition: 'bottom left',
  backgroundRepeat: 'no-repeat',
  [theme.breakpoints.down('md')]: {
    backgroundImage: 'none! important'
  },
  ...(multipleAccess && {
    position: 'relative'
  })
}));

const GridLeft = styled(Grid, {
  name,
  slot: 'GridLeftLogin',
  overridesResolver(props, styles) {
    return [styles.gridLeftLogin];
  }
})(({ theme }) => ({
  padding: `${theme.spacing(4)} ${theme.spacing(2)}`
}));

const FormContent = styled(Grid, {
  name
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  padding: theme.spacing(0, 0, 2)
}));

const WelcomeContent = styled(Box, {
  name
})(({ theme }) => ({
  backgroundPosition: 'bottom left',
  backgroundRepeat: 'no-repeat',
  display: 'flex',
  height: '100%',
  width: '100%',
  flexFlow: 'column',
  alignItems: 'center',
  textAlign: 'center'
}));

const ContentHeader = styled(Box, {
  name
})(({ theme }) => ({
  flexGrow: 1
}));

const Title = styled(Typography, {
  name
})(({ theme }) => ({
  fontWeight: 'bold',
  marginBottom: theme.spacing(2)
}));

const SubTitle = styled(Typography, {
  name,
  slot: 'SubTitleLogin',
  overridesResolver(props, styles) {
    return [styles.subTitleLogin];
  }
})(({ theme }) => ({
  paddingTop: theme.spacing(2)
}));

export interface LoginContentProps extends BlockViewProps {
  multipleAccess: boolean;
  title?: string;
  subTitle?: string;
  subTitle1?: string;
  subtitle2?: string;
  logo?: string;
  className?: string;
  limit: number;
  isShowFooter?: boolean;
  isShowFooterAppMenu?: boolean;
  background?: string;
  backgroundStyling?: string;
}

function LoginContent({
  limit = 4,
  isShowFooter = true,
  isShowFooterAppMenu = true,
  backgroundStyling = 'cover',
  background
}: LoginContentProps) {
  const { i18n, getSetting, jsxBackend } = useGlobal();
  const settingSiteBackground = getSetting('site-background.collection');

  const { accounts } = useSession();
  const FooterMenu = jsxBackend.get('core.block.footer');

  const multipleAccess = accounts && Array.isArray(accounts) && accounts.length;

  if (!accounts || !Array.isArray(accounts)) return null;

  const backgroundBlock = background
    ? `url(${background}) center/${backgroundStyling ?? 'cover'} no-repeat`
    : undefined;

  return (
    <Root
      sx={{
        background: theme =>
          backgroundBlock ??
          (settingSiteBackground ? 'transparent' : theme.palette.primary.main)
      }}
    >
      <ContainerWrapper maxWidth="md" gutter>
        <LoginLanguages/>
        <ContainerGrid container multipleAccess={multipleAccess}>
          <GridLeft item xs={12} md={6}>
            <WelcomeContent>
              <ContentHeader>
                <LogoBranch />
                <SubTitle
                  fontWeight={400}
                  variant="subtitle1"
                  color="textSecondary"
                >
                  {i18n.formatMessage({ id: 'login_slogan_message' })}
                </SubTitle>
              </ContentHeader>
            </WelcomeContent>
          </GridLeft>
          <FormContent item xs={12} md={6}>
            <Box sx={{ display: 'block', width: '100%' }}>
              <Title variant="h2" align="center">
                {i18n.formatMessage({ id: 'login_welcome_back' })}
              </Title>
              <LoginForm />
            </Box>
          </FormContent>
        </ContainerGrid>
        {isShowFooterAppMenu
          ? jsxBackend.render({
              component: 'core.block.appFooterMenu'
            })
          : null}
        {isShowFooter ? <FooterMenu color={'inherit'} /> : null}
      </ContainerWrapper>
    </Root>
  );
}

export default createBlock<LoginContentProps>({
  extendBlock: LoginContent,
  defaults: {
    title: 'Login Content',
    isShowFooter: true,
    isShowFooterAppMenu: true,
    backgroundStyling: 'cover'
  },
  custom: {
    background: {
      name: 'background',
      component: 'DirectUploadFile',
      label: 'Background Image',
      margin: 'normal'
    },
    backgroundStyling: {
      name: 'backgroundStyling',
      component: 'Dropdown',
      label: 'Background Image Styling',
      margin: 'normal',
      options: [
        { label: 'Cover', value: 'cover' },
        { label: 'Contain', value: 'contain' }
      ],
      showWhen: ['truthy', 'background']
    },
    divider: {
      name: 'divider',
      component: 'Divider',
      margin: 'normal',
      sx: {
        my: 2
      }
    },
    isShowFooterAppMenu: {
      name: 'isShowFooterAppMenu',
      component: 'Checkbox',
      label: 'Show footer app menu?',
      margin: 'normal'
    },
    isShowFooter: {
      name: 'isShowFooter',
      component: 'Checkbox',
      label: 'Show footer?',
      margin: 'normal'
    }
  }
});

/**
 * @type: block
 * name: user.block.userLoginContent
 * title: User Login Block
 * keywords: general
 * chunkName: boot
 */
import {
  AuthUserShape,
  BlockViewProps,
  createBlock,
  useGlobal,
  useSession
} from '@metafox/framework';
import { Container, LineIcon, Image } from '@metafox/ui';
import {
  Box,
  Grid,
  IconButton,
  Tooltip,
  Typography,
  styled
} from '@mui/material';
import React from 'react';
import LoginForm from './LoginForm';
import LoginLanguages from './LoginLanguages';
import UserAccessed from './UserAccessed';
import LogoBranch from './LogoBranch';

const Root = styled(Box, {
  name: 'LayoutSlot',
  slot: 'RootLogin',
  overridesResolver(props, styles) {
    return [styles.rootLogin];
  }
})(({ theme }) => ({
  minHeight: '100vh',
  display: 'flex',
  alignItems: 'center'
}));

const SignIn = styled(Box, {
  name: 'LayoutSlot',
  slot: 'SignIn',
  overridesResolver(props, styles) {
    return [styles.signIn];
  }
})(({ theme }) => ({}));

const ContainerWrapper = styled(Container, {
  name: 'LayoutSlot'
})(({ theme }) => ({
  display: 'flex',
  minHeight: '100vh',
  flexFlow: 'column',
  justifyContent: 'center',
  maxWidth: '1074px',
  paddingTop: theme.spacing(2.5),
  paddingLeft: theme.spacing(1.5),
  paddingRight: theme.spacing(1.5)
}));

const ContainerGrid = styled(Grid, {
  name: 'LayoutSlot',
  shouldForwardProp: prop => prop !== 'multipleAccess',
  slot: 'ContainerGridLogin',
  overridesResolver(props, styles) {
    return [styles.containerGridLogin];
  }
})<{ multipleAccess?: boolean }>(({ theme, multipleAccess }) => ({
  backgroundColor: theme.palette.background.paper,
  marginBottom: theme.spacing(5),
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
  name: 'LayoutSlot',
  slot: 'GridLeftLogin',
  overridesResolver(props, styles) {
    return [styles.gridLeftLogin];
  }
})(({ theme }) => ({
  padding: theme.spacing(2),
  marginBottom: theme.spacing(4),
  [theme.breakpoints.up('sm')]: {
    borderRight: 'solid 1px',
    borderRightColor: theme.palette.border?.secondary,
    padding: theme.spacing(6),
    marginBottom: 0
  }
}));

const FormContent = styled(Grid, {
  name: 'LayoutSlot'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  [theme.breakpoints.up('md')]: {
    padding: theme.spacing(6, 0)
  },
  [theme.breakpoints.down('md')]: {
    padding: theme.spacing(0, 0, 3)
  }
}));

const WelcomeContent = styled(Box, {
  name: 'LayoutSlot'
})(({ theme }) => ({
  backgroundPosition: 'bottom left',
  backgroundRepeat: 'no-repeat',
  display: 'flex',
  height: '100%',
  width: '100%',
  flexFlow: 'column'
}));

const ContentHeader = styled(Box, {
  name: 'LayoutSlot'
})(({ theme }) => ({
  flexGrow: 1
}));

const Title = styled(Typography, {
  name: 'LayoutSlot'
})(({ theme }) => ({
  fontWeight: 'bold',
  marginBottom: theme.spacing(2)
}));

const SubTitle = styled(Typography, {
  name: 'LayoutSlot',
  slot: 'SubTitleLogin',
  overridesResolver(props, styles) {
    return [styles.subTitleLogin];
  }
})(({ theme }) => ({
  paddingTop: theme.spacing(2)
}));

export interface LoginContentProps extends BlockViewProps {
  isShowMultipleAccess?: boolean;
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

type TestAccount = { email: string; password: string; enabled: boolean };

function LoginContent({
  limit = 4,
  isShowMultipleAccess = true,
  isShowFooter = true,
  isShowFooterAppMenu = true,
  backgroundStyling = 'cover',
  background
}: LoginContentProps) {
  const { dispatch, i18n, getSetting, assetUrl, useTheme, jsxBackend } =
    useGlobal();
  const settingSiteBackground = getSetting('site-background.collection');

  const { accounts } = useSession();
  const [user, setUser] = React.useState<AuthUserShape>();
  const testAccount = getSetting<TestAccount>('testAccount');
  const FooterMenu = jsxBackend.get('core.block.footer');

  const theme = useTheme();

  const isIpad = window.outerWidth < theme.breakpoints.values.md;

  const multipleAccess =
    isShowMultipleAccess &&
    accounts &&
    Array.isArray(accounts) &&
    accounts.length;

  const welcomeBg = multipleAccess
    ? assetUrl('layout.image_sign_in_multi_access')
    : assetUrl('layout.image_welcome');

  const addMoreAccount = () => dispatch({ type: 'user/addMoreAccount' });

  if (!accounts || !Array.isArray(accounts)) return null;

  const onSelectUser = (user: AuthUserShape) => setUser(user);

  const removeAccount = (user: AuthUserShape) => {
    dispatch({ type: 'user/removeAccount', payload: user.id });
  };

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
                {welcomeBg ? (
                  <Box py={1}>
                    <Image aspectRatio={'11'} src={welcomeBg} backgroundImage />
                  </Box>
                ) : null}
              </ContentHeader>
              {multipleAccess && !isIpad ? (
                <SignIn>
                  <Typography
                    variant="subtitle1"
                    paragraph
                    fontWeight={400}
                    color="textSecondary"
                  >
                    {i18n.formatMessage({ id: 'login_previously' })}
                  </Typography>
                  <Box display="flex" flexDirection="row">
                    {accounts.slice(0, limit).map((user, index) => (
                      <Box
                        key={index.toString()}
                        sx={{
                          marginRight: 3,
                          position: 'relative',
                          ':hover .closeBtn': {
                            visibility: 'visible'
                          }
                        }}
                      >
                        <UserAccessed
                          onSelectUser={onSelectUser}
                          user={user as any}
                          size={64}
                        />
                        <Tooltip
                          title={i18n.formatMessage({
                            id: 'remove_account_from_this_page'
                          })}
                        >
                          <IconButton
                            size="smallest"
                            onClick={() => removeAccount(user)}
                            className="closeBtn"
                            sx={{
                              visibility: 'hidden',
                              position: 'absolute',
                              right: -4,
                              transform: 'scale(0.8)',
                              top: 0,
                              backgroundColor: `${theme.palette.background.paper} !important`,
                              color: theme.palette.text.secondary,
                              padding: '0 !important'
                            }}
                          >
                            <LineIcon icon="ico-close" />
                          </IconButton>
                        </Tooltip>
                      </Box>
                    ))}
                    <Tooltip
                      title={i18n.formatMessage({ id: 'add_new_account' })}
                    >
                      <IconButton
                        color="primary"
                        aria-label="add more"
                        onClick={addMoreAccount}
                        sx={{ width: 64, height: 64, border: '1px solid' }}
                      >
                        <LineIcon icon="ico-plus" />
                      </IconButton>
                    </Tooltip>
                  </Box>
                </SignIn>
              ) : null}
            </WelcomeContent>
          </GridLeft>
          <FormContent item xs={12} md={6}>
            <Box sx={{ display: 'block', width: '100%' }}>
              <Title variant="h2" align="center">
                {i18n.formatMessage({ id: 'login_welcome_back' })}
              </Title>
              {testAccount?.enabled && testAccount.email ? (
                <Typography variant="body2" paragraph align="center">
                  Test Account: <b>{testAccount.email}</b>
                  <b>/{testAccount.password}</b>
                </Typography>
              ) : null}
              <LoginForm user={user} />
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
    isShowMultipleAccess: true,
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
    isShowMultipleAccess: {
      name: 'isShowMultipleAccess',
      component: 'Checkbox',
      label: 'Show previously signed in?',
      margin: 'normal'
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

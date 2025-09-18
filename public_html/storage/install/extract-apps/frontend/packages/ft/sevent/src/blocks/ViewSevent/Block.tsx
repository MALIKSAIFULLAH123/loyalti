/**
 * @type: block
 * name: sevent.block.seventView
 * title: Sevent Detail
 * keywords: sevent
 * description: Display sevent detail
 * experiment: true
 */
import { SeventDetailViewProps as Props } from '@ft/sevent';
import {
  FacebookIcon,
  FacebookShareButton,
  RedditIcon,
  RedditShareButton,
  TelegramIcon,
  TelegramShareButton,
  TwitterShareButton,
  WhatsappIcon,
  WhatsappShareButton,
  XIcon
} from 'react-share';
import { mappingTimeDisplay } from '@ft/sevent/blocks/ViewSevent/time';
import Grid from '@mui/material/Grid';
import actionCreators from '@ft/sevent/actions/seventItemActions';
import { SeventDetailViewProps as ItemProps } from '@ft/sevent/types';
import {
  connectItemView,
  connectSubject,
  createBlock,
  Link,
  useGlobal
} from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block } from '@metafox/layout';
import {
  AttachmentItem,
  DraftFlag,
  FeaturedFlag,
  ItemAction,
  ItemView,
  SponsorFlag,
  HtmlViewerWrapper
} from '@metafox/ui';
import { Box, styled, useTheme, Button } from '@mui/material';
import React, { useState, useRef } from 'react';
import Calendar from './Calendar';
import Status from './Status';
import AuthorInfo from './AuthorInfo';
import HostInfo from './HostInfo';
import Slider from './Slider';
import SeventTabs from './Tabs';
import GoogleMapComponent from './GoogleMapComponent';
import Attend from '../../components/Attend';

const PageTitle = styled(Box, { name: 'PageTitle' })(({ theme }) => ({
  margin: theme.spacing(1.5, 0, 3),
  fontWeight: 'bold',
  minWidth: 0,
  marginBottom: '4px',
  fontSize: '2em',
  lineHeight: 1.3,
  overflow: 'hidden',
  '& .ico-heart': {
    color: theme.palette.primary.main
  },
  [theme.breakpoints.down('sm')]: {
    fontSize: theme.spacing(3),
    margin: theme.spacing(1.5, 0, 2),
    color: theme.palette.text.primary
  }
}));

const SeventContent = styled('div', { name: 'SeventContent', slot: 'seventContent' })(
  ({ theme }) => ({
    fontSize: '15px',
    lineHeight: 1.5
  })
);
const TagItem = styled('div', { 
  name: 'TagItem', 
  slot: 'tagItem',
  overridesResolver(props, styles) {
   return [styles.tagItem];
 }
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  fontWeight: theme.typography.fontWeightBold,
  borderRadius: theme.shape.borderRadius / 2,
  background:
    theme.palette.mode === 'light'
      ? theme.palette.background.default
      : theme.palette.action.hover,
  marginRight: theme.spacing(1),
  marginBottom: theme.spacing(1),
  padding: theme.spacing(0, 1.5),
  height: theme.spacing(3),
  lineHeight: theme.spacing(3),
  display: 'block',
  color: theme.palette.mode === 'light' ? '#121212' : '#fff'
}));
const AttachmentTitle = styled('div', { name: 'AttachmentTitle', slot: 'attachmentTitle' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(18),
    marginTop: theme.spacing(2),
    color: theme.palette.text.secondary,
    fontWeight: theme.typography.fontWeightBold
  })
);
const Attachment = styled('div', { name: 'Attachment', slot: 'attachment' })(({ theme }) => ({
  width: '100%',
  display: 'flex',
  flexWrap: 'wrap',
  justifyContent: 'space-between',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));
const AttachmentItemWrapper = styled('div', {
  name: 'AttachmentItemWrapper',
  slot: 'attachmentItemWrapper'
})(({ theme }) => ({
  marginTop: theme.spacing(2),
  flexGrow: 0,
  flexShrink: 0,
  flexBasis: 'calc(50% - 8px)',
  minWidth: 300,
  maxWidth: '100%'
}));

export function LoadingSkeleton({ wrapAs, wrapProps }) {
  return <ItemView testid="skeleton" wrapAs={wrapAs} wrapProps={wrapProps} />;
}

export function DetailView({
  user,
  identity,
  item,
  state,
  actions,
  handleAction
}: ItemProps) {
  const theme = useTheme();
  const {
    ItemActionMenu,
    ItemDetailInteraction,
    i18n,
    useIsMobile,
    getSetting,
    ListView,
    useLoggedIn,
    useGetItems
  } = useGlobal();
  const isLogged = useLoggedIn();
  const isMobile = useIsMobile();
  const [value, setValue] = useState('campaign');
  const tabRefs = useRef();

  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );
  const attachedPhotos = useGetItems<{ id: number; name: string }>(
    item?.attach_photos
  );
  const isXlarge = window.innerWidth > 1919 ? true : false;
  const attachments = useGetItems(item?.attachments);
  const settingTime = getSetting('sevent.time_format') as number;
  const [startTime, endTime] = mappingTimeDisplay(
    item?.start_date,
    item?.end_date,
    settingTime === 24
  );

  if (!user || !item) return null;

  const shareUrl = window.location.href;
  const pageTitle = item.title;
  const { tags } = item;

  const terms = (
    <SeventContent>
          <HtmlViewerWrapper>
              <HtmlViewer html={(item?.terms || '')} />
          </HtmlViewerWrapper>
    </SeventContent>
  );

  const campaign = (
    <>
      <SeventContent>
          <HtmlViewerWrapper>
              <HtmlViewer html={(item?.text || '')} />
          </HtmlViewerWrapper>
      </SeventContent>
      {item.google_map_api_key && item.lat && item.lng ? (
        <Box style={{ maxWidth: '800px', margin: '16px 0' }}>
          <Box display='flex' style={{ padding: '8px 0' }} alignItems='center' justifyContent='space-between'>
            <h3 style={{ padding: 0, margin: 0 }}>
              {i18n.formatMessage({ id: 'sevent_map' })}
            </h3>
            <a href={`https://www.google.com/maps/dir/?api=1&destination=${item.lat},${item.lng}`}
            target="_blank"
            rel="noopener noreferrer"
            style={{ display: 'inline-flex', alignItems: 'center' }}
            >
              <i className='ico ico-car' style={{ paddingRight: '7px' }}></i>
              {i18n.formatMessage({ id: 'sevent_find_route' })}
            </a>
          </Box>
          <GoogleMapComponent item={item}/>
        </Box>
      ) : null}
      {tags?.length > 0 ? (
      <Box mt={4} display="flex" flexWrap="wrap">
          {tags.map(tag => (
          <TagItem key={tag}>
              <Link to={`/sevent/all?q=%23${encodeURIComponent(tag)}`}>
              {tag}
              </Link>
          </TagItem>
          ))}
      </Box>
      ) : null}
      {attachments?.length > 0 && (
      <>
          <AttachmentTitle>
          {i18n.formatMessage({ id: 'attachments' })}
          </AttachmentTitle>
          <Attachment>
          {attachments.map(item => (
              <AttachmentItemWrapper key={item.id.toString()}>
              <AttachmentItem
                  fileName={item.file_name}
                  downloadUrl={item.download_url}
                  isImage={item.is_image}
                  fileSizeText={item.file_size_text}
                  size="large"
                  image={item?.image}
                  identity={item?._identity}
              />
              </AttachmentItemWrapper>
          ))}
          </Attachment>
      </>
      )}
    </>
  );
  const comments = (
    <ItemDetailInteraction
          identity={identity}
          state={state}
          handleAction={handleAction}
      />
  );
  const ticket = (
    <ListView
        dataSource={{
          'apiParams': 'sevent_id=' + item.id + '&sort=amount',
          'apiUrl': '/sevent/ticket'
        }}
        canLoadMore={true}
        maxPageNumber={120}
        numberOfItemsPerPage={5}
        gridLayout='Sevent - Main Card'
        blockLayout='Main Listings'
        itemView='sevent_ticket.itemView.mainCard'
      />
  );
  const attending = (
    <ListView
        dataSource={{
          apiUrl: '/sevent/getAttending',
          apiParams: {
            sevent_id: item.id
          }
        }}
        canLoadMore={true}
        contentType='sevent'
        maxPageNumber={120}
        numberOfItemsPerPage={12}
        gridLayout='Sevent - 2'
        blockLayout='Main Listings'
        itemView='sevent.itemView.userCard'
      />
  );
  const interested = (
    <ListView
        dataSource={{
          apiUrl: '/sevent/getInterested',
          apiParams: {
            sevent_id: item.id
          }
        }}
        canLoadMore={true}
        contentType='sevent'
        maxPageNumber={120}
        numberOfItemsPerPage={12}
        gridLayout='Sevent - 2'
        blockLayout='Main Listings'
        itemView='sevent.itemView.userCard'
      />
  );

  const handleTabChange = () => {
    const element = tabRefs.current;
    const rect = element.getBoundingClientRect();
    const offset = -50; 
    const absoluteElementTop = rect.top + window.pageYOffset; 
    const scrollToPosition = absoluteElementTop + offset; 

    window.scrollTo({ top: scrollToPosition, behavior: 'smooth' });
  };

  const attendButton = isLogged ? (
    <Attend
      identity={identity}
      item={item}
      defaultRsvp={item.attend}
    />
  ) : null;
  
  return (
    <Block testid={`detailview ${item.resource_name}`} key={item.id}>
      <Box style={{ backgroundColor: theme.mixins.backgroundColor('paper'),
       marginTop: '-12px', padding: theme.spacing(4, 2, 4, 2) }}>
        <Box style={{ width: '100%', position: 'relative' }}>
          <Box display='flex' alignItems='center'>
            <Status item={item}/>
            <FeaturedFlag variant="itemView" value={item.is_featured} />
            <SponsorFlag
              variant="itemView"
              value={item.is_sponsor}
              item={item}
            />
            <DraftFlag
              value={item.is_draft}
              variant="h3"
              component="span"
              sx={{
                verticalAlign: 'middle',
                fontWeight: 'normal'
              }}
            />
          </Box>
          <PageTitle>{item.title}</PageTitle>
          <div style={{ color: theme.palette.text.secondary, fontSize: '14px', marginBottom: '8px' }}>
              <AuthorInfo item={item} categories={categories}/>
          </div>
          <div style={{ color: theme.palette.text.secondary, fontSize: '14px', marginBottom: '16px' }}>
              {item.short_description}
          </div>
          <ItemAction sx={{ position: 'absolute', top: 6, right: -5 }}>
            <ItemActionMenu
              identity={identity}
              icon={'ico-gear-o'}
              state={state}
              size='bigger'
              menuName="detailActionMenu"
              handleAction={handleAction}
            />
          </ItemAction>
      </Box>
        <Grid container spacing={3} style={{ maxWidth: '1350px' }}>
          <Grid item xs={12} md={7}>
              <Slider item={item} attachedPhotos={attachedPhotos} />
          </Grid>
          <Grid item xs={12} md={5}>
              <Box>
              {item.is_online === 1 ? (
                    <>
                      <Button
                        style={{
                         marginBottom: '32px'
                        }}
                        component="a"
                        href={item.online_link}
                        endIcon={<i className="ico ico-external-link" />} 
                        target="_blank"
                        rel="noopener noreferrer" 
                        variant="contained"
                        color="success"
                      >
                        {i18n.formatMessage({ id: 'sevent_online_registration' })}
                      </Button>
                    </>
                ) : (
                  <div style={{ marginBottom: '32px' }}>
                  <h3 style={{ margin: '0 0 8px' }}>
                    {i18n.formatMessage({ id: 'sevent_location' })}
                  </h3>
                  <div style={{ color:theme.palette.text.secondary, fontSize: '14px' }}>
                  <span style={{ display: 'inline-flex', gap: '8px', alignItems: 'flex-start' }}>
                      <i className='ico ico-checkin-o'/> {item.location_name}
                    </span>
                  </div>
                </div>
                )}
                <Box display='flex' flexDirection={!isMobile ? 'row' : 'column'} gap='32px'>
                  <div>
                    <h3 style={{ margin: '0 0 8px' }}>
                      {i18n.formatMessage({ id: 'sevent_start_time' })}
                    </h3>
                    <div style={{ color:theme.palette.text.secondary, fontSize: '14px',
                      display: 'inline-flex', gap: '8px', alignItems: 'center'
                     }}>
                    <i className='ico ico-clock-o'/>
                      {startTime}
                    </div>
                  </div>
                  <div>
                    <h3 style={{ margin: '0 0 8px' }}>
                      {i18n.formatMessage({ id: 'sevent_end_time' })}
                    </h3>
                    <div style={{ color:theme.palette.text.secondary, fontSize: '14px',
                      display: 'inline-flex', gap: '8px', alignItems: 'center'
                     }}>
                    <i className='ico ico-clock-o'/>
                      {endTime}
                    </div>
                  </div>
                </Box>
            </Box>
            <Box style={{ margin: '16px 0 32px', width: '100%' }} display='flex' gap='16px'>
              {attendButton}
              {isLogged && !item.is_expiry && item.statistic.total_ticket > 0 ? (
                <>
                  <div>
                        <Button style={{ 
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center'
                        }} 
                        startIcon={<i className='ico ico-ticket-o' />}
                        onClick={() => {
                          setValue('ticket');
                          handleTabChange();
                        }}
                          variant='contained' color='primary' fullWidth>
                          {i18n.formatMessage({ id: 'sevent_select_tickets' })}
                        </Button>
                    </div>
                </>
              ) : null}
            </Box>
            <h3 style={{ margin: '0' }}>
              {i18n.formatMessage({ id: 'sevent_share_event' })}
            </h3>
            <Box display='flex' justifyContent='space-between' alignItems='center' style={{ marginTop: '8px' }}>
              <Box display='flex' flexWrap='wrap' style={{ marginBottom: '-5px' }}>
                  <FacebookShareButton 
                    style={{ marginRight: '10px' }} 
                    url={shareUrl}
                    iconFillColor={theme.palette.text.secondary}
                    title={pageTitle}
                    className='Demo__some-network__share-button'>
                    <FacebookIcon size={22} round />
                  </FacebookShareButton>
                  <TwitterShareButton 
                    style={{ marginRight: '10px' }}
                    url={shareUrl}
                    title={pageTitle}
                    className='Demo__some-network__share-button'
                  >
                  <XIcon size={22} round />
                  </TwitterShareButton>
                  <TelegramShareButton 
                    style={{ marginRight: '10px' }}
                    url={shareUrl}
                    title={pageTitle}
                    className='Demo__some-network__share-button'
                >
                  <TelegramIcon size={22} round />
                </TelegramShareButton>
                <WhatsappShareButton 
                  style={{ marginRight: '10px' }}
                  url={shareUrl}
                  title={pageTitle}
                  separator=":: "
                  className='Demo__some-network__share-button'
                >
                  <WhatsappIcon size={22} round />
                </WhatsappShareButton>
                <RedditShareButton 
                  style={{ marginRight: '10px' }}
                  url={shareUrl}
                  title={pageTitle}
                  windowWidth={660}
                  windowHeight={460}
                  className='Demo__some-network__share-button'
                >
                  <RedditIcon size={22} round />
                </RedditShareButton>
              </Box>
              <Calendar item={item} isLogged={isLogged}/>
            </Box>
            {item.is_host && isXlarge ? (
              <div style={{ marginTop: '32px' }}>
                <HostInfo item={item} />
              </div>
            ) : null}
          </Grid>
        </Grid>
        {item.is_host && !isXlarge ? (
            <HostInfo item={item} />
        ) : null}
        <div style={{ background: theme.palette.background.default, 
          padding: isMobile ? '32px 16px' : '32px 0', margin: '32px -16px 8px' }}>
           <Grid container spacing={3}>
              <Grid item xs={12} md={4}>
                  <Box display='flex' gap='10px'>
                    <i className='ico ico-calendar-o' style={{ fontSize: '35px', color: 'green' }}/>
                    <div style={{ fontSize: '14px', color: theme.palette.text.secondary }}>
                      {i18n.formatMessage({ id: 'sevent_icon_1_description' })}
                    </div>
                  </Box>
              </Grid>
              <Grid item xs={12} md={4}>
                <Box display='flex' gap='10px'>
                    <i className='ico ico-ticket-o' style={{ fontSize: '35px', color: 'red' }}/>
                    <div style={{ fontSize: '14px', color: theme.palette.text.secondary }}>
                      {i18n.formatMessage({ id: 'sevent_icon_2_description' })}
                    </div>
                  </Box>
              </Grid>
              <Grid item xs={12} md={4}>
                <Box display='flex' gap='10px'>
                    <i className='ico ico-money-bag-o' style={{ fontSize: '35px', color: 'purple' }}/>
                    <div style={{ fontSize: '14px', color: theme.palette.text.secondary }}>
                      {i18n.formatMessage({ id: 'sevent_icon_3_description' })}
                    </div>
                  </Box>
              </Grid>
          </Grid>
        </div>
        <div style={{ display: 'none' }}>{ticket}{attending}{interested}</div>
        <SeventTabs attending={attending} interested={interested} 
          tabRefs={tabRefs} value={value} setValue={setValue} item={item}
          ticket={ticket} comments={comments} campaign={campaign} terms={terms}/>
      </Box>
      <Box style={{ background: theme.palette.background.default, 
          padding: isMobile ? '32px 16px' : '32px 0', margin: '32px 0 8px' }}>
        <ListView
          dataSource={{
            'apiParams': 'view=similar&ex=' + item.id,
            'apiUrl': '/sevent'
          }}
          canLoadMore={false}
          maxPageNumber={1}
          blockLayout='Profile - Side Contained (no header divider)'
          title={i18n.formatMessage({ id: 'similar_sevents' })}
          blockProps={{
            headerStyle: {
              sx: {
                p: '0px 0 16px',
                fontWeight: 'bold',
                fontSize: '16px'
              } 
            }
          }}
          itemProps={{
            itemSimilar: 'on'
          }}
          numberOfItemsPerPage={isXlarge ? 4 : 3}
          gridLayout='Sevent Lists'
          itemView='sevent.itemView.mainCard'
          emptyPage = 'hide'
        />
      </Box>      
    </Block>
  );
}

DetailView.LoadingSkeleton = LoadingSkeleton;
DetailView.displayName = 'SeventItem_DetailView';

const Enhance = connectSubject(
  connectItemView(DetailView, actionCreators, {
    categories: true,
    attachments: true
  })
);

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    blockLayout: 'Detail - Paper - Radius Bottom'
  }
});

/**
 * @type: siteFixedDock
 * name: SmartBanner
 */
import React from 'react';
import { useGlobal, IS_ADMINCP } from '@metafox/framework';
import SmartBanner from 'react-smartbanner';
import './styles.css';

interface SmartBannerProp {
  title?: string;
  price?: any;
  store_text?: string;
  button?: any;
  position?: 'top' | 'bottom';
}

export default function SiteSmartBanner() {
  const { getSetting } = useGlobal();
  const smartBanner: SmartBannerProp = getSetting('mobile.smart_banner');
  const { title, position, price, store_text, button } = smartBanner || {};

  if (IS_ADMINCP) return null;

  return (
    <SmartBanner
      title={title}
      daysHidden={7}
      daysReminder={0}
      position={position || 'bottom'}
      ignoreIosVersion
      price={price}
      storeText={store_text}
      button={button}
    />
  );
}

/**
 * @type: ui
 * name: chatplus.ui.flyChatRoomPanel
 */
import React from 'react';
import Base from './Base';

export default React.memo(Base, (prev, next) => prev.rid === next.rid);

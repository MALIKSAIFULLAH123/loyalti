import { ItemShape } from '@metafox/ui';

export interface TourGuideItemShape extends ItemShape {
  is_auto?: string;
  background_color?: string;
  delay?: number;
  font_color?: string;
  desc?: string;
  element?: string;
  ordering?: string;
  [key: string]: any;
}

export interface AppState {
  entities: {
    tourguide: Record<string, TourGuideItemShape>;
  };
  statusTourguide: {
    tourguide_id?: string;
    status?: StatusTourGuide;
    createStep?: TourGuideStep;
  };
}

interface TourGuidePositionType {
  x?: number;
  y?: number;
  position?: {
    top?: string;
    bottom?: string;
    right?: string;
    left?: string;
  };
  rotation?: number;
}

export interface TourGuideSettingType {
  tour_guide_button?: TourGuidePositionType;
}

export enum TourGuideStep {
  Init = 0,
  Tour = 1,
  SelectElement = 2,
  InputInfoStep = 3,
  Complete = 4
}

export enum StatusTourGuide {
  No = 0,
  Start = 1,
  Create = 2,
  Hidden = 3
}

export enum PauseStatus {
  No = 0,
  Pause = 1
}

export enum ActionTypeTour {
  create = 'tourguide/createTour',
  start = 'tourguide/startTour'
}

export interface StepItemType extends ItemShape {
  background_color: string;
  delay?: number;
  desc?: string;
  element?: string;
  font_color?: string;
  ordering?: number;
  title?: string;
}

export interface TourGuideContextProps {
  fire?: (any) => void;
  status: StatusTourGuide;
  createStep?: TourGuideStep;
  tourId?: string;
  step?: number;
  steps?: StepItemType[];
  stepItem?: StepItemType;
  totalStep?: number;
  pauseStatus?: PauseStatus;
  pageParams?: Record<string, string>;
  isMoveDock?: boolean;
  valueStepSubmit?: ValueStepSubmitType;
  initialStep?: number;
  hasDragDock?: boolean;
}

export interface ActionTourType {
  tourguide_id?: string;
  menu: MenuActionTourType[];
}
export interface MenuActionTourType {
  icon: string;
  label: string;
  value: string;
  name?: string;
  params?: any;
}

export interface PositionType {
  top?: string;
  bottom?: string;
  left?: string;
  right?: string;
}

export interface ValueStepSubmitType {
  background_color: string;
  delay?: number;
  font_color?: string;
}

export type OptionsType = {
  loop?: boolean;
  skipSnaps?: boolean;
  duration?: number;
  startIndex?: number;
  slides?: string | HTMLElement[] | NodeListOf<HTMLElement> | null;
  dragFree?: boolean;
  dragThreshold?: number;
  [key: string]: any;
};

export type EmblaCarouselType = {
  canScrollNext: () => boolean;
  canScrollPrev: () => boolean;
  containerNode: () => HTMLElement;
  internalEngine: () => any;
  destroy: () => void;
  off: any;
  on: any;
  emit: any;
  plugins: () => any;
  previousScrollSnap: () => number;
  reInit: (options?: OptionsType, plugins?: any[]) => void;
  rootNode: () => HTMLElement;
  scrollNext: (jump?: boolean) => void;
  scrollPrev: (jump?: boolean) => void;
  scrollProgress: () => number;
  scrollSnapList: () => number[];
  scrollTo: (index: number, jump?: boolean) => void;
  selectedScrollSnap: () => number;
  slideNodes: () => HTMLElement[];
  slidesInView: () => number[];
  slidesNotInView: () => number[];
};

export type useCarouselType = {
  carouselApi?: EmblaCarouselType;
  currentSelected: number;
  total: number;
  onPrev?: () => void;
  onNext?: () => void;
  prevBtnDisabled?: boolean;
  nextBtnDisabled?: boolean;
};

export interface ComponentCarouselProps {
  carouselApi: EmblaCarouselType;
  component?: React.ElementType;
  [key: string]: any;
}

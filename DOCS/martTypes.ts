// ProjectOptions, Pages, QuestionSheets and Scales are to be provided by the project creator.
// DeviceInfo, Stats,Submits will be send from the App.

type questionnaireOptions = {
  questionnaireId: number;
  startDateAndTime: { date: string; time: string };
  // Date and time when the questionnaire will be shown. Date in fromat "20.03.2025" and time in format "13:00"
  showProgressBar?: boolean;
  // if true, the progress bar will be shown on top of the questionnaire
  showNotifications?: boolean;
  // if true, notifications will be shown when the questionnaire is available
  notificationText?: string;
  // text of the notification
};

export type ProjectOptions = {
  projectId: number;
  projectName: string;
  // will not be used in the frontend, only for backend use
  options: {
    startDateAndTime: { date: string; time: string };
    // Date and time when the project starts, before that no questionnaires will be shown and a message is displayed. Date in fromat "20.03.2025" and time in format "13:00"
    endDateAndTime: { date: string; time: string };
    // Date and time when the project will be deactivated, after that no questionnaires will be shown and a message is displayed. Date in fromat "20.03.2025" and time in format "13:00"
    pages: number[];
    // pages that will be used in the project, is used to load the pages from the backend
    pagesToShowInMenu: number[];
    // pages that will be shown in the menu. Page IDs need to be in the array pages
    pagesToShowOnFirstAppStart: number[];
    // pages that will be shown on first app start, the order in which the pages are shown is the order of the array. Page IDs need to be in the array pages

    iOSDataDonationQuestionnaire?: number;
    // id of the questionnaire that will be shown when ios stats are collected, if not defined or null, no iOSStats Page will be shown.
    androidDataDonationQuestionnaire?: number;
    // id of the questionnaire that will be shown when android stats are collected, if not defined or null, no AndroidStats Page will be shown.
    minBreakBetweenDataDonationQuestionnaires?: number;
    // minimum break between stats questionnaires in hours
    minBreakBetweenAndroidStats?: number;
    // minimum break between android stats collection in hours, the collection is done automatically after a submit of single or repeating questionnaires.
    androidStatsPermissionPage?: number;
    // id of the page that will be shown when android stats permission is requested
    iOSNotificationPermissionPage?: number;
    // id of the page that will be shown when ios notification permission is requested
    androidNotificationPermissionPage?: number;
    // id of the page that will be shown when android notification permission is requested
    successPage?: number;
    // id of the page that will be shown when a questionnaire is finished
    collectAndroidStats: boolean;
    // if true, android stats will be collected in the background
    collectDeviceInfos: boolean;
    // if true, device infos will be collected
    initialHoursOfAndroidStats?: number;
    // how many hours of stats should be collected.
    overlapAndroidStatsHours?: number;
    // defining the overlap in hours from the last time android stats were collected. This makes sure no stats are missed.

    singleQuestionnaires?: // unlimited amount of single questionnaires can be created which will be shown on a specific date and time (startDateAndTime)
    (questionnaireOptions & {
      type: "single";
      showAfterRepeatingQ?: {
        repeatingQuestId: number;
        showAfterAmount: number;
      };
      // if set, the single questionnaire will be shown after the amount of repeating questionnaire of the defined repeating questionnaireId. Its a minimum check.
    })[];
    repeatingQuestionnaires?: (questionnaireOptions & {
      type: "repeating";

      endDateAndTime: { date: string; time: string };
      // Date and time when the repeating questionnaire will be shown for the last time. Date in fromat "20.03.2025" and time in format "13:00"

      minBreakBetweenQuestionnaire: number;
      // minimum break between questionnaires in minutes
      dailyIntervalDuration: number;
      // daily interval duration in hours, in each interval only one questionnaire will be shown
      maxDailySubmits: number;
      // maximum amount of questionnaires that can be submitted per day
      dailyStartTime: string;
      //  time on which the repeating questionnaire will be available. e.g. "06:00"
      dailyEndTime: string;
      //   time on which the repeating questionnaire will not be available. e.g. "22:00"
      questAvailableAt: "startOfInterval" | "randomTimeWithinInterval";
      // if "startOfInterval", the questionnaire will be available at the start of the interval
      // if "randomTimeWithinInterval", the questionnaire will be available at a random time within the interval
    })[];
  };
};

export type Page = {
  id: number;
  // id of the page, can be random
  pageId: number;
  // given unique Id of the page.
  name: string;
  // title of the page
  content: string;
  // content of the page, can be html
  options: {
    buttonText: string;
    // text of the button
  };
};

export type QuestionnaireItem = {
  itemId?: number | null;
  // itemId is only necessary if its a question, if its text only, the itemId is null
  scaleId?: number | null;
  //  id of the scale that will be used
  text?: string;
  imageUrl?: string;
  // url of an image that will be shown above the scale
  videoUrl?: string;
  // url of a video that will be shown above the scale
  options?: {
    randomizationGroupId?: number | null;
    // all subsequent items with the same randomizationGroupId will be randomized
    randomizeAnswers?: boolean;
    // if true, the answers will be randomized. Only for radio and checkbox scales.
    randomPositionWithinQuestionnaire?: boolean;
    // if true, the position of the item will be randomized within the questionnaire
    itemGroup?: string;
    // items with the same itemGroup will be shown together on the same page. e.G. "1"
    noValueAllowed?: boolean;
    // if true, the item can be skipped
  };
};

export type Questionnaire = {
  questionnaireId: number;
  name: string;
  // name will be shown in the overview of the app when a questionnaire is finished
  items: QuestionnaireItem[];
};

export type Scale = {
  scaleId: number;
  options: {
    defaultValue?: number | string | null;
    // default value can be set
    type:
      | "number"
      | "text"
      | "radio"
      | "checkbox"
      | "textarea"
      | "radioWithText"
      | "checkboxWithText"
      | "range"
      | "rangeValues"
      | "fileUpload"
      | "photoUpload"
      | "videoUpload"
      | "audioUpload";
    timer?: {
      // for timer questions
      time: number;
      // time in seconds
      showCountdown: boolean;
      //  if true, the countdown in secondswill be shown
    };
    jump?: {
      jumpCondition: string;
      // if the value of the checkbox or radio meets the value of the jumpCondition, a number of items defined in 'jumpOver' will be skipped
      jumpOver: number;
      // number of items to skip
    };
    fileUploadOptions?: {
      fileType: "image" | "video" | "audio" | "document";
      maxFileSize?: number;
      // maximum file size in MB
      allowedFormats?: string[];
      // allowed file formats e.g. ["jpg", "png", "pdf"]
    };

    photoUploadOptions?: {
      allowFileSelection?: boolean;
      // if true, the user can select a file from the gallery
      allowEditing?: boolean;
      // if true, the user can edit the photo
      quality?: number;
      // 90 is the default quality -> 1.5MB, quality of 50 -> 0.5MB
    };

    videoUploadOptions?: {
      allowFileSelection?: boolean;
      // if true, the user can select a file from the device
      maxVideoLength?: number;
      // maximum video length in seconds
      maxFileSize?: number;
      // maximum file size in MB
    };

    audioUploadOptions?: {
      maxFileSize?: number;
      // maximum file size in MB
      maxAudioLength?: number;
      // maximum audio length in seconds
      allowFileSelection?: boolean;
      // if true, the user can select a file from the device
    };

    maxValue?: number;
    // maximum value of the scale
    minValue?: number;
    // minimum value of the scale
    maxDigits?: number;
    // maximum amount of digits, for number scales
    radioOptions?: {
      // options of the radio scale
      value: number;
      text: string;
    }[];
    checkboxOptions?: {
      // options of the checkbox scale
      value: number | "text" | "textarea";
      text: string;
    }[];
    rangeOptions?: {
      // options of the range scale, for Number scale
      minValue: number;
      // minimum value of the range
      maxValue: number;
      // maximum value of the range
      steps: number;
      // amount of steps
      defaultValue: number;
      // default value of the range
    };
    rangeValueOptions?: {
      // options of the range scale, for Text
      value: number;
      text: string;
    }[];
    textOptions?: {
      type: "text" | "textarea";
      // type of the text scale
    };
  };
};

export type DeviceInfo = {
  projectId: number;
  userId: string;
  participantId: string;
  os: "android" | "ios";
  osVersion: string;
  model: string;
  manufacturer: string;
  timestamp: Number;
  timestampInherited: Number;
  // the timestamp of the questionnaire which triggered the sending of the device info
  timezone: string;
};

export type Stat = {
  projectId: number;
  userId: string;
  participantId: string;
  androidUsageStats: { [key: string]: any }[];
  androidEventStats: { [key: string]: any }[];
  timestamp: Number;
  // the timestamp of the previous questionnaire which triggered the sending of the Stat
  timezone: string;
};

export type Submit = {
  questionnaireId: number;
  projectId: number;
  userId: string;
  participantId: string;
  sheetId: Number;
  questionnaireStarted: Number;
  questionnaireDuration: Number;
  answers: { [key: string]: any };
  timestamp: Number;
  timestampInherited?: Number;
  // Only used if the questionnaire was an androidDataDonationQuestionnaire or iOSDataDonationQuestionnaire. Its the timestamp of the previous questionnaire which triggered the stats questionnaire
  timezone: string;
};

export type martDataFromBackend = {
  projectOptions: ProjectOptions;
  questionnaires: Questionnaire[];
  scales: Scale[];
  pages: Page[];
  deviceInfos: DeviceInfo[];
  repeatingSubmits: { questionnaireId: number; timestamp: number }[];
  singleSubmits: { questionnaireId: number; timestamp: number }[];
  lastDataDonationSubmit: { questionnaireId: number; timestamp: number };
  // The manually collected stats for Android or iOS through a questionnaire
  lastAndroidStatsSubmit: { timestamp: number };
  // The automatically collected Android stats
};

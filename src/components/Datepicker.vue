<template>
  <div class="vdp-datepicker" :class="[wrapperClass, isRtl ? 'rtl' : '']">
    <date-input
      :selectedDate="selectedDate"
      :resetTypedDate="resetTypedDate"
      :format="format"
      :language="language"
      :inline="inline"
      :id="id"
      :name="name"
      :refName="refName"
      :openDate="openDate"
      :placeholder="placeholder"
      :inputClass="inputClass"
      :typeable="typeable"
      :parse-typed-date="parseTypedDate"
      :clearButton="clearButton"
      :clearButtonIcon="clearButtonIcon"
      :calendarButton="calendarButton"
      :calendarButtonIcon="calendarButtonIcon"
      :calendarButtonIconContent="calendarButtonIconContent"
      :disabled="disabled"
      :required="required"
      :bootstrapStyling="bootstrapStyling"
      :use-utc="useUtc"
      :show-calendar-on-focus="showCalendarOnFocus"
      @showCalendar="showCalendar"
      @closeCalendar="close"
      @typedDate="setTypedDate"
      @clearDate="clearDate"
    >
      <slot name="beforeDateInput" slot="beforeDateInput"></slot>
      <slot name="afterDateInput" slot="afterDateInput"></slot>
    </date-input>

    <div class="vdp-datepicker__container" v-show="isOpen">
      <!-- Day View -->
      <picker-day
        v-if="allowedToShowView('day')"
        :pageDate="pageDate"
        :selectedDate="selectedDate"
        :showDayView="showDayView"
        :fullMonthName="fullMonthName"
        :allowedToShowView="allowedToShowView"
        :disabledDates="disabledDates"
        :highlighted="highlighted"
        :calendarClass="calendarClass"
        :language="language"
        :pageTimestamp="pageTimestamp"
        :mondayFirst="mondayFirst"
        :dayCellContent="dayCellContent"
        :use-utc="useUtc"
        @changedMonth="handleChangedMonthFromDayPicker"
        @selectDate="selectDate"
        @showMonthCalendar="showMonthCalendar"
        @selectedDisabled="selectDisabledDate"
      >
        <slot name="beforeCalendarHeader" slot="beforeCalendarHeader"></slot>
      </picker-day>

      <!-- Month View -->
      <picker-month
        v-if="allowedToShowView('month')"
        :pageDate="pageDate"
        :selectedDate="selectedDate"
        :showMonthView="showMonthView"
        :allowedToShowView="allowedToShowView"
        :disabledDates="disabledDates"
        :calendarClass="calendarClass"
        :language="language"
        :use-utc="useUtc"
        @selectMonth="selectMonth"
        @showYearCalendar="showYearCalendar"
        @changedYear="setPageDate"
      >
        <slot name="beforeCalendarHeader" slot="beforeCalendarHeader"></slot>
      </picker-month>

      <!-- Year View -->
      <picker-year
        v-if="allowedToShowView('year')"
        :pageDate="pageDate"
        :selectedDate="selectedDate"
        :showYearView="showYearView"
        :allowedToShowView="allowedToShowView"
        :disabledDates="disabledDates"
        :calendarClass="calendarClass"
        :language="language"
        :use-utc="useUtc"
        @selectYear="selectYear"
        @changedDecade="setPageDate"
      >
        <slot name="beforeCalendarHeader" slot="beforeCalendarHeader"></slot>
      </picker-year>
    </div>
  </div>
</template>
<script>
import { createPopper } from "@popperjs/core";
import DateInput from "./DateInput.vue";
import PickerDay from "./PickerDay.vue";
import PickerMonth from "./PickerMonth.vue";
import PickerYear from "./PickerYear.vue";
import utils, { makeDateUtils, rtlLangs } from "../utils/DateUtils.js";

export default {
  components: {
    DateInput,
    PickerDay,
    PickerMonth,
    PickerYear
  },
  props: {
    value: {
      validator: val => utils.validateDateInput(val)
    },
    name: String,
    refName: String,
    id: String,
    format: {
      type: [String, Function],
      default: "DD MMM YYYY"
    },
    outputFormat: {
      type: String,
      default: "YYYY-MM-DD"
    },
    language: {
      type: String,
      default: "bg"
    },
    openDate: {
      validator: val => utils.validateDateInput(val)
    },
    dayCellContent: Function,
    fullMonthName: Boolean,
    disabledDates: Object,
    highlighted: Object,
    placeholder: String,
    inline: Boolean,
    calendarClass: [String, Object, Array],
    inputClass: [String, Object, Array],
    wrapperClass: [String, Object, Array],
    mondayFirst: Boolean,
    clearButton: Boolean,
    clearButtonIcon: String,
    calendarButton: Boolean,
    calendarButtonIcon: String,
    calendarButtonIconContent: String,
    bootstrapStyling: Boolean,
    initialView: String,
    disabled: Boolean,
    required: Boolean,
    typeable: Boolean,
    parseTypedDate: Function,
    useUtc: Boolean,
    minimumView: {
      type: String,
      default: "day"
    },
    maximumView: {
      type: String,
      default: "year"
    },
    showCalendarOnFocus: Boolean,
    placement: {
      type: String,
      default: "auto",
      validator: function(value) {
        return ["top", "bottom", "auto"].indexOf(value) !== -1;
      }
    }
  },
  data() {
    const startDate = this.openDate ? new Date(this.openDate) : new Date();
    const constructedDateUtils = makeDateUtils(this.useUtc, this.language);
    const pageTimestamp = constructedDateUtils.setDate(startDate, 1);
    return {
      /*
       * Vue cannot observe changes to a Date Object so date must be stored as a timestamp
       * This represents the first day of the current viewing month
       * {Number}
       */
      pageTimestamp,
      /*
       * Selected Date
       * {Date}
       */
      selectedDate: null,
      /*
       * Flags to show calendar views
       * {Boolean}
       */
      showDayView: false,
      showMonthView: false,
      showYearView: false,
      /*
       * Positioning
       */
      calendarHeight: 0,
      resetTypedDate: new Date(),
      utils: constructedDateUtils
    };
  },
  watch: {
    language(newLanguage) {
      this.utils = makeDateUtils(this.useUtc, newLanguage);
    },
    useUtc(newUtc) {
      this.utils = makeDateUtils(newUtc, this.language);
    },
    value(value) {
      this.setValue(value);
    },
    openDate() {
      this.setPageDate();
    },
    initialView() {
      this.setInitialView();
    },
    isInline(val) {
      if (val) {
        this.initPopper();
      } else {
        this.destroyPopper();
      }
    },
    isOpen(val) {
      if (val) {
        //console.log('watcher is open => true call init popper')
        this.initPopper();
      } else {
        //console.log('watcher is open => false call destroy popper')
        this.destroyPopper();
      }
    },
    placement() {
      this.setPlacement();
    },
    isRtl() {
      this.setPlacement();
    }
  },
  computed: {
    computedInitialView() {
      if (!this.initialView) {
        return this.minimumView;
      }

      return this.initialView;
    },
    pageDate() {
      return new Date(this.pageTimestamp);
    },
    isOpen() {
      return this.showDayView || this.showMonthView || this.showYearView;
    },
    isInline() {
      return !!this.inline;
    },
    isRtl() {
      return rtlLangs.indexOf(this.language) !== -1;
    }
  },
  methods: {
    /**
     * Called in the event that the user navigates to date pages and
     * closes the picker without selecting a date.
     */
    resetDefaultPageDate() {
      if (this.selectedDate === null) {
        this.setPageDate();
        return;
      }
      this.setPageDate(this.selectedDate);
    },
    /**
     * Effectively a toggle to show/hide the calendar
     * @return {mixed}
     */
    showCalendar() {
      if (this.disabled || this.isInline) {
        return false;
      }
      if (this.isOpen) {
        return this.close(true);
      }
      this.setInitialView();
      this.$emit("opened");
    },
    /**
     * Sets the initial picker page view: day, month or year
     */
    setInitialView() {
      const initialView = this.computedInitialView;
      if (!this.allowedToShowView(initialView)) {
        throw new Error(
          `initialView '${this.initialView}' cannot be rendered based on minimum '${this.minimumView}' and maximum '${this.maximumView}'`
        );
      }
      switch (initialView) {
        case "year":
          this.showYearCalendar();
          break;
        case "month":
          this.showMonthCalendar();
          break;
        default:
          this.showDayCalendar();
          break;
      }
    },
    /**
     * Are we allowed to show a specific picker view?
     * @param {String} view
     * @return {Boolean}
     */
    allowedToShowView(view) {
      const views = ["day", "month", "year"];
      const minimumViewIndex = views.indexOf(this.minimumView);
      const maximumViewIndex = views.indexOf(this.maximumView);
      const viewIndex = views.indexOf(view);

      return viewIndex >= minimumViewIndex && viewIndex <= maximumViewIndex;
    },
    /**
     * Show the day picker
     * @return {Boolean}
     */
    showDayCalendar() {
      if (!this.allowedToShowView("day")) {
        return false;
      }
      this.close();
      this.showDayView = true;
      return true;
    },
    /**
     * Show the month picker
     * @return {Boolean}
     */
    showMonthCalendar() {
      if (!this.allowedToShowView("month")) {
        return false;
      }
      this.close();
      this.showMonthView = true;
      return true;
    },
    /**
     * Show the year picker
     * @return {Boolean}
     */
    showYearCalendar() {
      if (!this.allowedToShowView("year")) {
        return false;
      }
      this.close();
      this.showYearView = true;
      return true;
    },
    /**
     * Set the selected date
     * @param {Number} timestamp
     */
    setDate(timestamp) {
      const date = new Date(timestamp);
      this.selectedDate = date;
      this.setPageDate(date);
      this.$emit("selected", moment(date).format(this.outputFormat));
      this.$emit("input", moment(date).format(this.outputFormat));
    },
    /**
     * Clear the selected date
     */
    clearDate() {
      this.selectedDate = null;
      this.setPageDate();
      this.$emit("selected", null);
      this.$emit("input", null);
      this.$emit("cleared");
    },
    /**
     * @param {Object} date
     */
    selectDate(date) {
      this.setDate(date.timestamp);
      if (!this.isInline) {
        this.close(true);
      }
      this.resetTypedDate = new Date();
    },
    /**
     * @param {Object} date
     */
    selectDisabledDate(date) {
      this.$emit("selectedDisabled", date);
    },
    /**
     * @param {Object} month
     */
    selectMonth(month) {
      const date = new Date(month.timestamp);
      if (this.allowedToShowView("day")) {
        this.setPageDate(date);
        this.$emit("changedMonth", month);
        this.showDayCalendar();
      } else {
        this.selectDate(month);
      }
    },
    /**
     * @param {Object} year
     */
    selectYear(year) {
      const date = new Date(year.timestamp);
      if (this.allowedToShowView("month")) {
        this.setPageDate(date);
        this.$emit("changedYear", year);
        this.showMonthCalendar();
      } else {
        this.selectDate(year);
      }
    },
    /**
     * Set the datepicker value
     * @param {Date|String|Number|null} date
     */
    setValue(date) {
      if (typeof date === "string" || typeof date === "number") {
        let parsed = this.utils.parseDate(date);
        date = isNaN(parsed.valueOf()) ? null : parsed;
      }
      if (!date) {
        this.setPageDate();
        this.selectedDate = null;
        return;
      }
      this.selectedDate = date;
      this.setPageDate(date);
    },
    /**
     * Sets the date that the calendar should open on
     */
    setPageDate(date) {
      if (!date) {
        if (this.openDate) {
          date = new Date(this.openDate);
        } else {
          date = new Date();
        }
      }
      this.pageTimestamp = this.utils.setDate(new Date(date), 1);
    },
    /**
     * Handles a month change from the day picker
     */
    handleChangedMonthFromDayPicker(date) {
      this.setPageDate(date);
      this.$emit("changedMonth", date);
    },
    /**
     * Set the date from a typedDate event
     */
    setTypedDate(date) {
      this.setDate(date.getTime());
    },
    /**
     * Close all calendar layers
     * @param {Boolean} emitEvent - emit close event
     */
    close(emitEvent) {
      this.showDayView = this.showMonthView = this.showYearView = false;
      if (!this.isInline) {
        if (emitEvent) {
          this.$emit("closed");
        }
        document.removeEventListener("click", this.clickOutside, false);
      }
    },
    /**
     * Initiate the component
     */
    init() {
      if (this.value) {
        this.setValue(this.value);
      }
      if (this.isInline) {
        this.setInitialView();
      }
      /* if (!this.isInline) {
        this.initPopper();
      } */
    },
    /**
     * Initiate Popper.js
     */
    initPopper() {
      /* console.log('init popper') */
      if (this.popper) {
   /*      console.log('existing instance destroy')
        this.destroyPopper(); */
      }
/*       const direction = this.isRtl ? "end" : "start";
      const placement = `${this.placement}-${direction}` */
      const refEl = this.$el.querySelector("input");
      const popperEl = this.$el.querySelector(".vdp-datepicker__calendar");
      //console.log('create popper instance')
      this.popper = createPopper(refEl, popperEl, { placement: 'top' });
    },
    /**
     * Set Popper Placement
     */
    setPlacement() {
      /* const direction = this.isRtl ? "end" : "start";
      const placement = `${this.placement}-${direction}`;
      this.popper.setOptions({placement: 'auto'}) */
      this.$nextTick(() => {
        this.popper.forceUpdate();
      });
    },
    /**
     * Destroy Popper.js
     */
    destroyPopper() {
      //console.log('destroy popper instance !!')
      this.popper.destroy();
      this.popper = null;
    }
  },
  mounted() {
    this.init();
  }
};
// eslint-disable-next-line
</script>
<style lang="scss">
$color_1: #ddd;
$color_2: #a3a3a3;
$color_3: #888;
$color_4: #999;
$text-primary: #546e7a;
$text-secondary: #a0aec0;
//$purrple: #817ae3;
$purrple: #88acff;

.rtl {
  direction: rtl;
}
.vdp-datepicker {
  text-align: left;
  * {
    box-sizing: border-box;
  }
}
.vdp-datepicker__calendar {
  //popper changes
  position: absolute;
 
  //position: fixed;
  z-index: 100;
  background: #fff;
  width: 240px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  font-size: 12px;
  padding: 8px;
  header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-transform: capitalize;
    span {
      display: flex;
      align-items: center;
      justify-content: center;
      width: auto;
      cursor: pointer;
      color: $text-primary;
      padding: 7px;
    }
    .prev {
      width: 32px;
      padding: 10px;
      font-size: 12px;
      border-radius: 999px;
      margin-left: auto;
      &:not(.disabled) {
        cursor: pointer;
        &:hover {
          background: #eee;
        }
      }
    }
    .next {
      width: 32px;
      padding: 10px;
      font-size: 12px;
      border-radius: 999px;
      &:not(.disabled) {
        cursor: pointer;
        &:hover {
          background: #eee;
        }
      }
    }
    .prev.disabled {
      &:after {
        border-right: 10px solid #ddd;
      }
    }
    .next.disabled {
      &:after {
        border-left: 10px solid #ddd;
      }
    }
    .up {
      &:not(.disabled) {
        justify-content: start;
        align-items: baseline;
        width: auto;
        cursor: pointer;
        text-transform: capitalize;
        font-weight: 500;
        &:hover {
          background: #eee;
        }
      }
      & i {
        margin-left: 4px;
      }
    }
  }
  .disabled {
    color: $color_1;
    cursor: default;
  }
  .flex-rtl {
    display: flex;
    width: inherit;
    flex-wrap: wrap;
  }
  .cell {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    width: 32px;
    padding: 10px;
    color: $text-primary;
    font-size: 12px;
    border-radius: 999px;
    margin-bottom: 1px;
    &:not(.blank) {
      &:not(.disabled).day {
        cursor: pointer;
        &:hover {
          background-color: #f5f5f5;
        }
      }
      &:not(.disabled).month {
        cursor: pointer;
        border-radius: 2px;
        justify-content: center;
        margin: 2px;
        width: 70px;
        border: 1px solid #f5f5f5;
        &:hover {
          background-color: #f5f5f5;
        }
      }
      &:not(.disabled).year {
        cursor: pointer;
        border-radius: 2px;
        justify-content: center;
        margin: 2px;
        width: 70px;
        border: 1px solid #f5f5f5;
        &:hover {
          background-color: #f5f5f5;
        }
      }
    }
  }
  .cell.selected {
    color: #fff;
    background: $purrple;
    &:hover {
      background: $text-primary !important;
    }
  }
  .cell.selected.highlighted {
    color: #fff;
    background-color: $purrple;
  }
  .cell.highlighted {
    color: #b1b1b1;
  }
  .cell.highlighted.disabled {
    color: $color_2;
  }
  .cell.grey {
    color: $color_3;
    &:hover {
      background: inherit;
    }
  }
  .cell.day-header {
    font-size: 10px;
    color: $text-secondary;
    white-space: nowrap;
    border-bottom: 1px solid #ececec;
    border-radius: unset;
    text-transform: lowercase;
    margin: 4px 0;
    padding: 0 0 8px 0;
    cursor: inherit;
    &:hover {
      background: inherit;
    }
  }
  .month {
    width: 33.333%;
  }
  .year {
    width: 33.333%;
  }
}
.vdp-datepicker__clear-button {
  cursor: pointer;
  font-style: normal;
}
.vdp-datepicker__calendar-button {
  cursor: pointer;
  font-style: normal;
}
.vdp-datepicker__clear-button.disabled {
  color: $color_4;
  cursor: default;
}
.vdp-datepicker__calendar-button.disabled {
  color: $color_4;
  cursor: default;
}
.vdp-datepicker__main {
  display: flex;
  flex-wrap: wrap;
  width: 100%;
}
</style>

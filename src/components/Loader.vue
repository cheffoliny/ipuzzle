<template>
  <div
    :style="fitIn ? `height:${height}px; width:${width}px; position:${position};` : ''"
    :class="preloader ? (fillScreen ? 'loader min-h-screen' : 'loader') : (fillScreen ? 'min-h-screen loader b-drop' : 'loader b-drop')"
  >
    <div v-if="preloader" class="fa-5x">
      <i :class="`${preloaderIcon} fa-spin text-blue-450`"></i>
    </div>
    <div v-else class="fa-3x">
      <i :class="`${loaderIcon} fa-spin text-blue-450`"></i>
    </div>
  </div>
</template>

<script>
export default {
  name: "loader",
  props: {
    fitIn: {
      default: null
    },
    position: {
      type: String,
      default: "fixed"
    },
    preloader: {
      type: Boolean,
      default: false
    },
    fillScreen: {
      type: Boolean,
      default: false
    },
    preloaderIcon: {
      type: String,
      default: "fas fa-cog"
    },
    loaderIcon: {
      type: String,
      default: "fad fa-cog"
    }
  },
  data() {
    return {
      height: null,
      width: null
    };
  },
  mounted() {
    if (this.fitIn) {
      let el = document.getElementById(this.fitIn).getBoundingClientRect();
      this.height = el.height;
      this.width = el.width;
    }
  }
};
</script>

<style @scoped>
.loader {
  /* position: absolute; */
  width: 100%;
  height: 100%;
  z-index: 20;
  display: flex;
  align-items: center;
  justify-content: center;
  /*background-color: hsla(0, 0%, 96%, 0.72);*/
}
.b-drop {
  background-color: hsla(0, 0%, 96%, 0.72);
}
.min-h-screen {
  position: fixed;
  min-width: 100vw;
  min-height: 100vh;
  left: 0;
  top: 0;
}
</style>
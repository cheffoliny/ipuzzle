<template>
<!--   <transition name="modal"> -->
    <div  class="fixed-center-on-screen">
      <div :id="id" :style="{ 'z-index': zindexValue}" class="flex flex-col mwh-90" v-click-outside="close">
        <slot name="header"></slot>
        <slot name="body"></slot>
        <slot name="footer"></slot>
        <Loader :fitIn="id" v-if="loading" />
      </div>
    </div>
    
  <!-- </transition> -->
</template>

<script>
import vClickOutside from "v-click-outside";
import Loader from "./Loader.vue";

export default {
  name: "BaseDialog",
  components: {Loader},
  directives: {
    clickOutside: vClickOutside.directive
  },
  props: {
    static: {
      default: true
    },
    zindex: {
      default: 1000
    },
    id: {
      type: String,
      default: ''
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    zindexValue() {
      return this.zindex ? this.zindex : 1000
    }
  },
  methods: {
    close() {
      if (this.static) return;
      this.$emit("close");
    }
  },
};
</script>

<style lang="scss" scoped>
.fixed-center-on-screen {
  display: flex;
  align-items: center;
  justify-content: center;
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1040;
  transition: opacity 180ms cubic-bezier(0.4, 0, 0.2, 1);
}
.mwh-90 {
  max-width: 90%;
  max-height: 90%;
  box-shadow: 0 5px 5px -3px rgba(0, 0, 0, 0.2),
    0 8px 10px 1px rgba(0, 0, 0, 0.14), 0 3px 14px 2px rgba(0, 0, 0, 0.12);
  //transition: all 0.3s ease;
  transition: all 180ms cubic-bezier(0.4, 0, 0.2, 1);
}
@media screen and (max-width: 567px) {
  .mwh-90 {
    max-width: 100%;
    max-height: 100%;
  }
}
.modal-enter {
  opacity: 0;
}

.modal-leave-active {
  opacity: 0;
}

.modal-enter .mwh-90, .modal-enter-active,
.modal-leave-active .mwh-90 {
  transform: scale(1.1);
}
</style>

import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    count: 0,
    graph: {
      yAxisAttribute: "media",
      formatter: false
    },
  },
  mutations: {
    switchyAxisAttribute(state) {
      if (state.graph.yAxisAttribute === "media") {
        state.graph.yAxisAttribute = "inputs";
      } else if (state.graph.yAxisAttribute === "inputs") {
        state.graph.yAxisAttribute = "media";
      }
    },
    switchFormatter(state) {
      state.graph.formatter = !state.graph.formatter;
    },
  }
});

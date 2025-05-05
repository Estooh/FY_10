import { defineStore } from 'pinia';

export const useResultsStore = defineStore('results_store', {
  state: () => ({
    results: {
      status: '', // pass or fail
      score: 0,
      total_questions: 0,
      percentage: '',
    },
  }),
  getters: {
    // Define your getters here
  },
  actions: {
    onUpdate(status, score, total_questions, percentage) {
      this.results = {
        status,
        score,
        total_questions,
        percentage,
      };
    },
  },
});

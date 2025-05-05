import { defineStore } from 'pinia';
import { api } from 'src/boot/axios';

export const useNotesStore = defineStore('notes', {
  state: () => ({
    notes: null,
  }),
  getters: {
    hasNotes() {
      return this.notes !== null;
    },
  },
  actions: {
    setNotes(data) {
      this.notes = data; // Set the notes here
    },
   
  },
});

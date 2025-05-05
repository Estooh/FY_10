import { defineStore } from 'pinia';

export const useHomework = defineStore('homework', {
  state: () => ({
    homework: {

    },
  }),
  getters: {
    
  },
  actions: {
    createHomework(data) {
      this.homework = data;
      return this.homework;
    },
    readHomework() {
      return this.homework;
    },
    updateHomework(data) {
      this.homework = { ...this.homework, ...data };
      return this.homework;
    },
      deleteHomework(id) {
        const index = this.homework.findIndex(homework => homework.id === id);
        if (index !== -1) {
          this.homework.splice(index, 1);
        }
        return this.homework;
      },
  },
});

<template>
  <q-layout view="hHh LpR fFf" class="bg-grey-1">
    <q-header elevated class="bg-white text-grey-8" height-hint="64">
      <q-toolbar class="GNL__toolbar">
        <q-btn
          flat
          dense
          round
          @click="toggleLeftDrawer"
          aria-label="Menu"
          icon="menu"
          class="q-mr-sm"
        />

        <q-toolbar-title
          v-if="$q.screen.gt.xs"
          shrink
          class="row items-center no-wrap"
        >
          <q-item to="/" class="row items-center">
            <img src="~assets/conture-logo.png" width="50px" height="auto" />
          <span class="q-ml-sm">Jinoe</span>
          </q-item>
        </q-toolbar-title>

        <q-space />

        <q-space />

        <div class="q-gutter-sm row items-center no-wrap">

          <q-btn round dense flat color="grey-8" icon="notifications">
            <q-badge color="red" text-color="white" floating> 2 </q-badge>
            <q-tooltip>Notifications</q-tooltip>
          </q-btn>
          <q-btn round flat>
            <q-avatar size="26px">
              <img src="https://cdn.quasar.dev/img/boy-avatar.png" />
            </q-avatar>
            <q-tooltip>Account</q-tooltip>

          <q-menu>
            <div class="row no-wrap q-pa-md">
              <div class="column items-center justify-center">
                <q-avatar size="59px">
                  <img src="https://cdn.quasar.dev/img/boy-avatar.png" />
                </q-avatar>

                <q-btn
                  v-close-popup
                  color="primary"
                  label="Logout"
                  push
                  size="sm"
                  @click="logout"
                />
              </div>
            </div>
          </q-menu>

          </q-btn>
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      class="bg-white"
      :width="280"
    >
      <q-scroll-area class="fit">
        <q-list padding class="text-grey-8">
          <q-item class="">COURSES</q-item>
          <q-item
            class="GNL__drawer-item"
            v-ripple
            v-for="link in links1"
            :key="link.text"
            clickable
            :href="link.to"
            exact
          >
            <q-item-section avatar>
              <q-icon :name="link.icon" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ link.text }}</q-item-label>
            </q-item-section>
          </q-item>
          <q-space />
          <q-separator inset class="q-my-sm" />
          <q-item class="">ACCOUNT</q-item>
          <q-item
            class="GNL__drawer-item"
            v-ripple
            v-for="link in links2"
            :key="link.text"
            :href="link.to"
            clickable
          >
            <q-item-section avatar>
              <q-icon :name="link.icon" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ link.text }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-scroll-area>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from "vue";
import {
  fasFeatherPointed,
  fasBook,
  fasBrain,
  fasLayerGroup,
  fasList,
} from "@quasar/extras/fontawesome-v6";

const leftDrawerOpen = ref(false);

const links1 = [
  { icon: fasBook, text: "Notes", to: "/app/notes" },
  { icon: fasFeatherPointed, text: "Homework", to: "/app/subjects" },
  { icon: fasBrain, text: "Lifelong Studies", to: "/app/lifelong-studies" },
];
const links2 = [
  { icon: fasLayerGroup, text: "My Notes", to: "/app/my-notes" },
  { icon: "book", text: "My Homework", to: "/app/my-homework" },
  { icon: fasList, text: "Recent Activity", to: "/app/recent-activity" },
  { icon: 'person', text: "My Account", to: "/app/my-account" }, // This points to the route that will render Setting.vue
];

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value;
}

function logout() {
  // Perform logout logic and redirect to login page
  sessionStorage.removeItem('user');
  window.location.href = '/auth/login';
}
</script>

<style lang="sass">
.GNL

  &__toolbar
    height: 64px

  &__toolbar-input
    width: 55%

  &__drawer-item
    line-height: 24px
    border-radius: 0 24px 24px 0
    margin-right: 12px

    .q-item__section--avatar
      .q-icon
        color: #5f6368

    .q-item__label
      color: #3c4043
      letter-spacing: .01785714em
      font-size: .875rem
      font-weight: 500
      line-height: 1.25rem

  &__drawer-footer-link
    color: inherit
    text-decoration: none
    font-weight: 500
    font-size: .75rem

    &:hover
      color: #000
</style>

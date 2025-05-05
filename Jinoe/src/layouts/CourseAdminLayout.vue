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

        <!-- Container for Notification and Account Buttons -->
        <div class="q-gutter-sm row items-center no-wrap">
          <!-- Notification Button -->
          <q-btn round dense flat color="grey-8" icon="notifications">
            <q-badge color="red" text-color="white" floating>2</q-badge>
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
            :to="link.to"
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

const user = JSON.parse(sessionStorage.getItem('user'));
const leftDrawerOpen = ref(false);
const menuOpen = ref(false);

const links1 = [
  { icon: fasBook, text: "Notes", to: "/c/notes" },
  { icon: fasFeatherPointed, text: "Homework", to: "/c/home-work" },
  { icon: fasBrain, text: "Lifelong Studies", to: "/c/lifelong-studies" },
];

let links2 = [];

if (user.role === 'course-moderator') {
  links2 = [
    { icon: fasList, text: "Recent Activity", to: "/c/recent-activity" },
    { icon: 'person', text: "My Account", to: "/c/my-account" },
  ];
} else {
  links2 = [
    { icon: fasLayerGroup, text: "My Notes", to: "/c/my-notes" },
    { icon: "book", text: "My Homework", to: "/c/my-homework" },
    { icon: fasList, text: "Recent Activity", to: "/c/recent-activity" },
    { icon: 'person', text: "My Account", to: "/c/my-account" },
  ];
}

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value;
}

function goToProfile() {
  // Redirect to the profile page
  window.location.href = '/c/my-account';
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

.header-actions
  display: flex
  align-items: center

  .avatar-btn
    display: flex
    align-items: center

  .profile-menu
    .q-menu__content
      min-width: 120px
      padding: 0
      border-radius: 8px
      box-shadow: 0 2px 8px rgba(0,0,0,0.2)
      
      .q-list
        padding: 0

    .q-item
      border-radius: 8px
      &:hover
        background-color: #f0f0f0
</style>

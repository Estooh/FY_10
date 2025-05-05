<template>
    <q-page class="q-pa-md flex flex-center">
      <q-card style="max-width: 500px; width: 100%;">
        <q-card-section>
          <div class="text-h6 text-center">Profile and Account Management</div>
        </q-card-section>
  
        <q-separator />
  
    
        <!-- Change Password Section -->
        <q-card-section>
          <div class="text-subtitle2 text-center q-mb-md">Change Password</div>
          <div class="form-container">
            <q-input v-model="passwords.current" label="Current Password" type="password" outlined class="input-padding" :rules="[val => !!val || 'Current password is required']" />
            <q-input v-model="passwords.new" label="New Password" type="password" outlined class="input-padding" :rules="[val => !!val || 'New password is required']" />
            <q-input v-model="passwords.confirm" label="Confirm New Password" type="password" outlined class="input-padding" :rules="[val => !!val || 'Please confirm your new password']" />
          </div>
          <q-btn label="Change Password" @click="changePassword" color="primary" class="q-mt-md full-width" />
        </q-card-section>
      </q-card>
    </q-page>
  </template>
  
  <script>
    export default {
    data() {
      return {
        userInfo: {
          name: '',
          email: '',
          profilePicture: '', // Add profile picture field
        },
        passwords: {
          current: '',
          new: '',
          confirm: '',
        },
        defaultProfilePicture: 'path/to/default/profile/picture.png', // Set a default profile picture
      };
    },
    methods: {
      async fetchUserProfile() {
        try {
          const response = await this.$axios.get('/api/user/profile'); // API endpoint
          this.userInfo = response.data; // Populate user info
        } catch (error) {
          console.error('Failed to fetch user profile:', error);
          this.$q.notify({ color: 'negative', message: 'Failed to load user profile' });
        }
      },
      updateUserInfo() {
        // Handle the logic to update user information
        if (this.userInfo.name && this.userInfo.email) {
          // Call API to update user information
          this.$axios.put('/api/user/profile', this.userInfo)
            .then(() => {
              this.$q.notify({ color: 'positive', message: 'User information updated successfully!' });
            })
            .catch(error => {
              console.error('Failed to update user information:', error);
              this.$q.notify({ color: 'negative', message: 'Failed to update user information' });
            });
        } else {
          this.$q.notify({ color: 'negative', message: 'Please fill in all required fields!' });
        }
      },
      changePassword() {
        // Ensure the password fields are filled out and match
        if (this.passwords.current && this.passwords.new && this.passwords.confirm) {
          if (this.passwords.new === this.passwords.confirm) {
            // Call API to change the password
            this.$q.notify({ color: 'positive', message: 'Password changed successfully!' });
          } else {
            this.$q.notify({ color: 'negative', message: 'Passwords do not match!' });
          }
        } else {
          this.$q.notify({ color: 'negative', message: 'Please fill in all required password fields!' });
        }
      },
      onProfilePictureClick() {
        // Trigger file input click event to open file dialog
        this.$refs.fileInput.click();
      },
      onFileChange(event) {
        const file = event.target.files[0];
        if (file) {
          // Handle the file upload
          const reader = new FileReader();
          reader.onload = e => {
            this.userInfo.profilePicture = e.target.result; // Update profile picture
          };
          reader.readAsDataURL(file);
        }
      },
    },
    mounted() {
      this.fetchUserProfile(); // Fetch user profile on component mount
    },
  };
  
  </script>
  
  <style scoped>
  .full-width {
    width: 100%;
  }
  
  .form-container {
    max-width: 300px;
    margin: 0 auto;
  }
  
  .input-padding {
    padding-left: 10px; /* Add padding to the left */
    padding-top: 20px;
  }
  
  .q-mt-md {
    margin-top: 16px;
  }
  
  .q-mb-md {
    margin-bottom: 16px;
  }
  
  .profile-picture-container {
    position: relative;
  }
  
  .avatar {
    cursor: pointer;
  }
  
  .camera-icon {
    position: absolute;
    bottom: 0;
    right: 0;
    background-color: white;
    border-radius: 50%;
    padding: 4px;
  }
  </style>
  
<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        My Dashboard
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li>
            <router-link to="/dashboard" class="nav-link">
              Dashboard
            </router-link>
          </li>
          <li>
            <router-link to="/profile" class="nav-link">
              Profile
            </router-link>
          </li>
          <li>
            <router-link to="/settings" class="nav-link">
              Settings
            </router-link>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <header class="header">
        <h1 class="header-title">Dashboard</h1>
        <button @click="logout" class="logout-button">Logout</button>
      </header>

      <!-- Content -->
      <main class="content">
        <div class="welcome-section">
          <h2 class="welcome-title">Welcome, {{ user.name }}!</h2>
          <p class="welcome-text">Here's an overview of your account.</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
          <div class="stat-card">
            <h3 class="stat-title">Total Logins</h3>
            <p class="stat-value">42</p>
          </div>
          <div class="stat-card">
            <h3 class="stat-title">Projects</h3>
            <p class="stat-value">7</p>
          </div>
          <div class="stat-card">
            <h3 class="stat-title">Notifications</h3>
            <p class="stat-value">3</p>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

export default defineComponent({
  name: 'Dashboard',
  setup() {
    const router = useRouter();
    const user = ref({ name: 'Loading...' });

    const fetchUser = async () => {
      try {
        const response = await fetch('http://127.0.0.1:8000/api/user', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
          },
        });
        if (response.ok) {
          const data = await response.json();
          user.value = data;
        } else {
          console.error('Failed to fetch user data');
        }
      } catch (error) {
        console.error('Error fetching user:', error);
      }
    };

    const logout = async () => {
  try {
    // Send a request to the server to invalidate the session
    await fetch('http://127.0.0.1:8000/api/logout', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
      },
    });

    // Clear client-side authentication data
    localStorage.removeItem('token');
    // If you're using Vuex or any state management, reset the user state here

    // Redirect to the login page
    router.push('/auth/');
  } catch (error) {
    console.error('Error during logout:', error);
    // Handle errors appropriately
  }
};



    onMounted(() => {
      fetchUser();
    });

    return {
      user,
      logout,
    };
  },
});
</script>

<style scoped>
.dashboard-container {
  display: flex;
  height: 100vh;
  background-color: #f3f4f6;
  font-family: Arial, sans-serif;
}

/* Sidebar */
.sidebar {
  width: 250px;
  background-color: #ffffff;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}

.sidebar-header {
  padding: 20px;
  font-size: 1.5rem;
  font-weight: bold;
  color: #2563eb;
  border-bottom: 1px solid #e5e7eb;
}

.sidebar-nav {
  margin-top: 20px;
}

.sidebar-nav ul {
  list-style: none;
  padding: 0;
}

.nav-link {
  display: block;
  padding: 12px 20px;
  color: #374151;
  text-decoration: none;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.nav-link:hover {
  background-color: #dbeafe;
}

/* Main Content */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* Header */
.header {
  background-color: #ffffff;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e5e7eb;
}

.header-title {
  font-size: 1.75rem;
  font-weight: bold;
  color: #1f2937;
}

.logout-button {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 0.9rem;
  transition: text-decoration 0.2s;
}

.logout-button:hover {
  text-decoration: underline;
}

/* Content */
.content {
  flex: 1;
  padding: 30px;
  overflow-y: auto;
}

.welcome-section {
  margin-bottom: 30px;
}

.welcome-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
}

.welcome-text {
  color: #6b7280;
  margin-top: 5px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-card {
  background-color: #ffffff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-title {
  font-size: 0.9rem;
  color: #6b7280;
  margin-bottom: 10px;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: #2563eb;
}
</style>

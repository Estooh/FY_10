<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">My Dashboard</div>
      <nav class="sidebar-nav">
        <ul>
          <li><router-link to="/dashboard" class="nav-link">Dashboard</router-link></li>
          <li><router-link to="/profile" class="nav-link">Profile</router-link></li>
          <li><router-link to="/settings" class="nav-link">Settings</router-link></li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <header class="header">
        <h1 class="header-title">Dashboard</h1>
        <button @click="logout" class="logout-button">Logout</button>
      </header>

      <main class="content">
        <div class="welcome-section">
          <h2 class="welcome-title">Welcome, {{ user.full_name || 'User' }}!</h2>
          <p class="welcome-text">Here's an overview of your account.</p>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <h3 class="stat-title">Total Activity</h3>
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

        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const user = ref({});
const errorMessage = ref('');

const fetchUser = async () => {
  const token = localStorage.getItem('token');
  if (!token) {
    router.push('/auth/');
    return;
  }

  try {
    const response = await fetch('http://127.0.0.1:8000/api/dashboard', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    if (response.status === 401) {
      throw new Error('Unauthorized');
    }

    const data = await response.json();
    user.value = data;
  } catch (err) {
    console.error('Fetch user error:', err);
    localStorage.removeItem('token');
    errorMessage.value = 'Session expired or unauthorized. Redirecting...';
    setTimeout(() => router.push('/auth/'), 2000);
  }
};

const logout = async () => {
  try {
    const token = localStorage.getItem('token');
    await fetch('http://127.0.0.1:8000/api/logout', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    });
  } catch (err) {
    console.warn('Logout failed:', err);
  } finally {
    localStorage.removeItem('token');
    router.push('/auth/');
  }
};

onMounted(fetchUser);
</script>

<style scoped>
/* Same styles as before (no change) */
.dashboard-container {
  display: flex;
  height: 100vh;
  background-color: #f3f4f6;
  font-family: Arial, sans-serif;
}

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

.sidebar-nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
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

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

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
}

.logout-button:hover {
  text-decoration: underline;
}

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

.error-message {
  margin-top: 20px;
  color: red;
  font-weight: bold;
}
</style>

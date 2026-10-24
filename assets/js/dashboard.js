const API = {
  getToken() {
    return localStorage.getItem('invito_token');
  },

  getUser() {
    const raw = localStorage.getItem('invito_user');
    return raw ? JSON.parse(raw) : null;
  },

  setAuth(token, user) {
    localStorage.setItem('invito_token', token);
    localStorage.setItem('invito_user', JSON.stringify(user));
  },

  clearAuth() {
    localStorage.removeItem('invito_token');
    localStorage.removeItem('invito_user');
  },

  async request(method, url, body = null) {
    const headers = { 'Content-Type': 'application/json' };
    const token = this.getToken();
    if (token) {
      headers['Authorization'] = 'Bearer ' + token;
    }

    const opts = { method, headers };
    if (body !== null) {
      opts.body = JSON.stringify(body);
    }

    const res = await fetch(url, opts);
    const data = await res.json();

    if (!res.ok) {
      if (res.status === 401) {
        this.clearAuth();
        window.location.href = window.BASE_URL || '/';
        return;
      }
      throw new Error(data.error || 'Request failed');
    }
    return data;
  },

  get(url) { return this.request('GET', url); },
  post(url, body) { return this.request('POST', url, body); },
  put(url, body) { return this.request('PUT', url, body); },
  del(url, body) { return this.request('DELETE', url, body); },
};

function h(val) {
  const div = document.createElement('div');
  div.textContent = val;
  return div.innerHTML;
}

function showAlert(msg, type = 'error') {
  const el = document.getElementById('alert');
  if (!el) return;
  el.textContent = msg;
  el.className = 'alert alert-' + type;
  el.style.display = 'block';
  setTimeout(() => { el.style.display = 'none'; }, 5000);
}

function showLoading(show = true) {
  const el = document.getElementById('loading');
  if (el) el.style.display = show ? 'flex' : 'none';
}

async function checkAuth() {
  const token = API.getToken();
  const user = API.getUser();
  if (!token || !user) {
    window.location.href = window.BASE_URL || '/';
    return null;
  }
  return user;
}

function logout() {
  const token = API.getToken();
  if (token) {
    API.post((window.BASE_URL || '/') + 'api/auth/logout.php', { token }).catch(() => {});
  }
  API.clearAuth();
  window.location.href = window.BASE_URL || '/';
}

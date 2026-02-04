<header class="topbar">
  <div class="brand"><a href="/" class="brand-link">Lab Bahasa</a></div>
  <nav class="nav">
    <a href="/about">About</a>
    <?php if (session('admin_id')): ?>
      <a href="/admin/settings">⚙ Pengaturan</a>
    <?php elseif (session('participant_id')): ?>
      <a href="/student/settings">⚙ Pengaturan</a>
    <?php endif; ?>
    <?php if (session('admin_id') || session('participant_id')): ?>
      <a href="/logout">Logout</a>
    <?php endif; ?>
  </nav>
</header>

<style>
  .topbar {
    background: #ffffff;
    color: #0b1a1a;
    box-shadow: 0 1px 3px rgba(15, 25, 20, 0.1);
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
  }

  .topbar .brand {
    font-weight: 700;
    font-size: 18px;
  }

  .topbar .brand-link {
    color: #0b1a1a;
    text-decoration: none;
  }

  .topbar .nav {
    display: flex;
    gap: 28px;
  }

  .topbar .nav a {
    color: #0b1a1a;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s;
  }

  .topbar .nav a:hover {
    color: #2fa36f;
  }

  @media (max-width: 600px) {
    .topbar {
      flex-direction: column;
      gap: 12px;
      padding: 12px 16px;
    }

    .topbar .nav {
      width: 100%;
      justify-content: center;
      gap: 16px;
    }
  }
</style>

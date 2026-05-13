//   * Global function for form visibility
function showForm(formId) {
  document.querySelectorAll('.form-box').forEach((box) => {
    box.classList.remove('active');
  });

  const targetForm = document.getElementById(formId);
  if (targetForm) {
    targetForm.classList.add('active');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const links = document.querySelectorAll('.sidebar-link');
  const mainContent = document.getElementById('main-content');

  //   * Page Loading Logic
  const loadPage = async (section) => {
    try {
      const response = await fetch(`views/${section}.php`);
      if (!response.ok) throw new Error('Page not found');

      const html = await response.text();
      mainContent.innerHTML = html;

      // Update Active Link State
      links.forEach((l) => {
        l.classList.toggle('active', l.getAttribute('data-section') === section);
      });
    } catch (error) {
      mainContent.innerHTML = `<h1>Error</h1><p>Could not load the page.</p>`;
    }
  };

  //   * Sidebar Link Click Handling

  links.forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const section = link.getAttribute('data-section');
      window.history.pushState({ section: section }, '', `?page=${section}`);
      loadPage(section);
    });
  });

  //   * MODAL TOGGLE HANDLING (Using Event Delegation)
  mainContent.addEventListener('click', (e) => {
    // Helper to get current local time formatted for datetime-local input
    const getNowTimestamp = () => {
      const now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
      return now.toISOString().slice(0, 16);
    };

    const currentTimestamp = getNowTimestamp();

    // 1. Open Add Resource Modal
    if (e.target.closest('#toggle-add-form')) {
      const overlay = document.getElementById('modal-overlay');
      if (overlay) overlay.classList.add('active');
    }

    // 2. Open Reserve Modal
    const reserveBtn = e.target.closest('.open-reserve-btn');
    if (reserveBtn) {
      const overlay = document.getElementById('reserve-modal-overlay');
      const inputId = document.getElementById('reserve-resource-id');
      const display = document.getElementById('res-name-display');

      if (overlay && inputId) {
        inputId.value = reserveBtn.getAttribute('data-id');
        if (display) display.innerText = reserveBtn.getAttribute('data-resource-name');

        // PREVENT PAST DATES: Set min attribute for both start and end
        const startInput = overlay.querySelector('input[name="start_datetime"]');
        const endInput = overlay.querySelector('input[name="end_datetime"]');
        if (startInput) startInput.min = currentTimestamp;
        if (endInput) endInput.min = currentTimestamp;

        overlay.classList.add('active');
      }
    }

    // 3. Open Use Modal & Auto-fill Current Time
    const useBtn = e.target.closest('.open-use-btn');
    if (useBtn) {
      const overlay = document.getElementById('use-modal-overlay');
      const inputId = document.getElementById('use-resource-id');
      const inputStartTime = document.getElementById('use-start-time');
      const inputEndTime = overlay.querySelector('input[name="end_datetime"]');
      const display = document.getElementById('use-name-display');

      if (overlay && inputId) {
        inputId.value = useBtn.getAttribute('data-id');
        if (display) display.innerText = useBtn.getAttribute('data-resource-name');

        // Auto-fill current time and prevent past end dates
        if (inputStartTime) {
          inputStartTime.value = currentTimestamp;
          inputStartTime.min = currentTimestamp;
        }
        if (inputEndTime) inputEndTime.min = currentTimestamp;

        overlay.classList.add('active');
      }
    }

    // 4. Close Logic (Combined for all modals)
    const isCloseBtn = e.target.closest('#close-modal') || e.target.closest('#close-reserve-modal') || e.target.closest('#close-use-modal');

    const isOverlayBackground = e.target.id === 'modal-overlay' || e.target.id === 'reserve-modal-overlay' || e.target.id === 'use-modal-overlay';

    if (isCloseBtn || isOverlayBackground) {
      ['modal-overlay', 'reserve-modal-overlay', 'use-modal-overlay'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('active');
      });
    }
  });

  // DYNAMIC DATE VALIDATION (End Date must be after Start Date)
  mainContent.addEventListener('input', (e) => {
    if (e.target.name === 'start_datetime') {
      const form = e.target.closest('form');
      const endInput = form.querySelector('input[name="end_datetime"]');
      if (endInput) {
        endInput.min = e.target.value;
      }
    }
  });

  //   FORM SUBMISSION HANDLING (AJAX)

  mainContent.addEventListener('submit', async (e) => {
    if (e.target && e.target.classList.contains('ajax-form')) {
      e.preventDefault();

      const form = e.target;
      const formData = new FormData(form);
      const targetUrl = form.getAttribute('action') || 'views/resources.php';

      try {
        const response = await fetch(targetUrl, { method: 'POST', body: formData });

        if (response.ok) {
          // Hide all potential overlays on success
          ['modal-overlay', 'reserve-modal-overlay', 'use-modal-overlay'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('active');
          });

          const currentSection = new URLSearchParams(window.location.search).get('page') || 'dashboard';
          loadPage(currentSection);

          alert('Action completed successfully!');
        } else {
          const errorMsg = await response.text();
          alert('Error: ' + errorMsg);
        }
      } catch (error) {
        console.error('Submission error:', error);
        alert('Connection error. Please try again.');
      }
    }
  });

  //   History & Initial Load handling
  window.addEventListener('popstate', (e) => {
    if (e.state && e.state.section) {
      loadPage(e.state.section);
    }
  });

  const urlParams = new URLSearchParams(window.location.search);
  const initialSection = urlParams.get('page') || 'dashboard';
  loadPage(initialSection);
});

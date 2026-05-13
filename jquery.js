/**
 * Global function for manual form toggling
 */
function showForm(formId) {
  $('.form-box').removeClass('active');
  $(`#${formId}`).addClass('active');
}

$(function () {
  const $mainContent = $('#main-content');
  const $links = $('.sidebar-link');

  /*
   * 1. PAGE LOADER (jQuery AJAX)
   */
  const loadPage = (section, queryString = '') => {
    const url = queryString ? `views/${section}.php?${queryString}` : `views/${section}.php`;

    $.get(url)
      .done(function (html) {
        $mainContent.html(html);

        // Update active state in sidebar
        $links.each(function () {
          $(this).toggleClass('active', $(this).data('section') === section);
        });
      })
      .fail(function () {
        $mainContent.html(`<h1>Error</h1><p>Could not load the page.</p>`);
      });
  };

  /*
   * 2. SIDEBAR NAVIGATION
   */
  $links.on('click', function (e) {
    e.preventDefault();
    const section = $(this).data('section');
    window.history.pushState({ section: section }, '', `?page=${section}`);
    loadPage(section);
  });

  /*
   * 3. MODAL & INTERACTION HANDLING (Delegated)
   */
  $mainContent.on('click', function (e) {
    const $target = $(e.target);

    const getNowTimestamp = () => {
      const now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
      return now.toISOString().slice(0, 16);
    };
    const currentTimestamp = getNowTimestamp();

    // ADD MEMBER MODAL
    if ($target.closest('#toggle-add-form').length) {
      $('#modal-overlay').addClass('active');
    }

    // RESERVE MODAL
    const $reserveBtn = $target.closest('.open-reserve-btn');
    if ($reserveBtn.length) {
      const $overlay = $('#reserve-modal-overlay');
      $('#reserve-resource-id').val($reserveBtn.data('id'));
      $('#res-name-display').text($reserveBtn.data('resource-name'));
      $overlay.find('input[name="start_datetime"], input[name="end_datetime"]').attr('min', currentTimestamp);
      $overlay.addClass('active');
    }

    // USE MODAL
    const $useBtn = $target.closest('.open-use-btn');
    if ($useBtn.length) {
      const $overlay = $('#use-modal-overlay');
      $('#use-resource-id').val($useBtn.data('id'));
      $('#use-name-display').text($useBtn.data('resource-name'));
      $('#use-start-time').val(currentTimestamp).attr('min', currentTimestamp);
      $overlay.find('input[name="end_datetime"]').attr('min', currentTimestamp);
      $overlay.addClass('active');
    }

    // UPDATE RESOURCE MODAL
    // UPDATE RESOURCE MODAL
    const $updateBtn = $target.closest('.open-update-btn');
    if ($updateBtn.length) {
      const $overlay = $('#update-modal-overlay');
      $('#update-resource-id').val($updateBtn.data('id'));
      $('#update-resource-name').val($updateBtn.data('name'));
      $('#edit-name-display').text($updateBtn.data('name'));
      $('#update-category').val($updateBtn.data('cat'));

      // ADD THIS LINE:
      $('#update-status').val($updateBtn.data('status'));

      $overlay.addClass('active');
    }

    // --- NEW: EDIT MEMBER MODAL ---
    const $editMemberBtn = $target.closest('.open-edit-member-btn');
    if ($editMemberBtn.length) {
      const $overlay = $('#edit-member-modal-overlay');
      // Populate fields from data attributes
      $('#edit-member-id').val($editMemberBtn.data('id'));
      $('#edit-first-name').val($editMemberBtn.data('fname'));
      $('#edit-last-name').val($editMemberBtn.data('lname'));
      $('#edit-email').val($editMemberBtn.data('email'));
      $('#edit-role').val($editMemberBtn.data('role'));
      $('#edit-tr-code').val($editMemberBtn.data('trcode'));
      $overlay.addClass('active');
    }

    // CLOSE LOGIC
    const isCloseBtn = $target.closest('#close-modal, #close-reserve-modal, #close-use-modal, #close-update-modal, #close-edit-member-modal').length;
    const isOverlayBg = ['modal-overlay', 'reserve-modal-overlay', 'use-modal-overlay', 'update-modal-overlay', 'edit-member-modal-overlay'].includes(
      e.target.id
    );

    if (isCloseBtn || isOverlayBg) {
      $('#modal-overlay, #reserve-modal-overlay, #use-modal-overlay, #update-modal-overlay, #edit-member-modal-overlay').removeClass('active');
    }
  });

  /*
   * 4. DYNAMIC DATE VALIDATION
   */
  $mainContent.on('input', 'input[name="start_datetime"]', function () {
    const $form = $(this).closest('form');
    $form.find('input[name="end_datetime"]').attr('min', $(this).val());
  });

  /*
   * 5. FORM SUBMISSION (jQuery AJAX with FormData)
   */
  $mainContent.on('submit', '.ajax-form', function (e) {
    e.preventDefault();
    const $form = $(this);
    const formData = new FormData(this);
    const targetUrl = $form.attr('action') || 'views/resources.php';

    $.ajax({
      url: targetUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function () {
        // Close all possible modals
        $('#modal-overlay, #reserve-modal-overlay, #use-modal-overlay, #update-modal-overlay, #edit-member-modal-overlay').removeClass('active');

        const currentSection = new URLSearchParams(window.location.search).get('page') || 'dashboard';
        loadPage(currentSection);
        alert('Action completed successfully!');
      },
      error: function (xhr) {
        alert('Error: ' + xhr.responseText);
      }
    });
  });

  /**
   * 6. BROWSER NAVIGATION & INITIAL LOAD
   */
  $(window).on('popstate', (e) => {
    const state = e.originalEvent.state;
    if (state && state.section) {
      loadPage(state.section);
    }
  });

  /*
   * 7. SEARCH & FILTER HANDLING (AJAX Submit)
   */
  $mainContent.on('submit', '.filter-form', function (e) {
    e.preventDefault();
    const section = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    const queryString = $(this).serialize();
    loadPage(section, queryString);
  });

  /*
   * 8. LIVE SEARCH & FILTER (Typing/Dropdown Change)
   */
  $mainContent.on('input change', '.filter-form input, .filter-form select', function () {
    const $form = $(this).closest('.filter-form');
    const section = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    const queryString = $form.serialize();

    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(function () {
      $.get(`views/${section}.php?${queryString}`)
        .done(function (html) {
          const newTable = $(html).find('.resources').html();
          $('.resources').html(newTable);
        })
        .fail(function () {
          console.error('Live search failed.');
        });
    }, 300);
  });

  /*
   * 9. DASHBOARD CLICKABLE CARDS
   */
  $mainContent.on('click', '.clickable', function () {
    const statusId = $(this).data('status');
    const section = 'resources';
    const query = `filter_status=${statusId}`;
    window.history.pushState({ section: section }, '', `?page=${section}&${query}`);
    loadPage(section, query);
  });

  /*
   * 10. RESET FILTERS (AJAX)
   */
  $mainContent.on('click', '.reset-filters', function () {
    const $form = $(this).closest('.filter-form');
    $form[0].reset();
    $form.find('input').first().trigger('change');
  });

  // INITIAL LOAD LOGIC
  const urlParams = new URLSearchParams(window.location.search);
  const initialSection = urlParams.get('page') || 'dashboard';
  urlParams.delete('page');
  const initialQuery = urlParams.toString();

  loadPage(initialSection, initialQuery);
});

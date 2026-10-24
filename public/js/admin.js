// Generic image-field upload: each .dropzone wraps a hidden <input type=file>
// (native <label for> makes the whole box clickable) and also accepts a
// dragged-and-dropped file. Either path uploads immediately via AJAX and
// writes the returned relative path into the paired hidden input, so the
// surrounding <form> submits that path like any other text field. The
// actual file upload happens out-of-band before submit.

function prepareImage(image) {
  if (!image.getAttribute('src')) return;

  image.classList.add('image-skeleton');
  const loaded = () => image.classList.remove('image-skeleton');
  if (image.complete) loaded();
  else {
    image.addEventListener('load', loaded, { once: true });
    image.addEventListener('error', loaded, { once: true });
  }
}

document.querySelectorAll('img').forEach((image) => {
  if (!image.hasAttribute('loading')) image.loading = image.closest('.admin-sidebar, .auth-page') ? 'eager' : 'lazy';
  if (!image.hasAttribute('decoding')) image.decoding = 'async';
  prepareImage(image);
});

function uploadImage(file, input) {
  if (!file) return;

  const allowedTypes = ['image/jpeg', 'image/png'];
  const extension = file.name.split('.').pop().toLowerCase();
  const allowedExtensions = ['jpg', 'jpeg', 'png'];
  if (!allowedExtensions.includes(extension) || (file.type && !allowedTypes.includes(file.type))) {
    adminToast('Pilih gambar JPG, JPEG, atau PNG.', 'error');
    input.value = '';
    return;
  }
  if (file.size > 2 * 1024 * 1024) {
    adminToast('Ukuran gambar maksimal 2 MB.', 'error');
    input.value = '';
    return;
  }

  const targetInput = document.getElementById(input.dataset.target);
  const preview = document.getElementById(input.dataset.preview);
  const dropzone = document.getElementById(input.dataset.dropzone);
  const csrf = document.querySelector('meta[name="csrf-token"]').content;

  const formData = new FormData();
  formData.append('image', file);

  dropzone.classList.add('is-uploading');

  fetch('/bojong/uploads', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
    body: formData,
  })
    .then((r) => {
      if (!r.ok) throw new Error('Upload failed');
      return r.json();
    })
    .then((data) => {
      targetInput.value = data.path;
      preview.classList.add('image-skeleton');
      preview.addEventListener('load', () => preview.classList.remove('image-skeleton'), { once: true });
      preview.addEventListener('error', () => preview.classList.remove('image-skeleton'), { once: true });
      preview.src = data.url;
      preview.classList.remove('is-empty');
    })
    .catch(() => {
      adminToast('Gambar gagal diunggah. Gunakan JPG, JPEG, atau PNG dengan ukuran maksimal 2 MB.', 'error');
    })
    .finally(() => {
      dropzone.classList.remove('is-uploading');
    });
}

document.addEventListener('change', function (e) {
  if (!e.target.classList.contains('js-image-upload')) return;
  uploadImage(e.target.files[0], e.target);
});

// Mobile sidebar toggle
(function () {
  const toggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('adminSidebar');
  if (!toggle || !sidebar) return;
  toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  document.addEventListener('click', (e) => {
    if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggle) {
      sidebar.classList.remove('open');
    }
  });
})();

// Toggle switches (visibility / show-in-nav) submit their own tiny form on change
document.addEventListener('change', function (e) {
  if (e.target.matches('.js-auto-submit')) {
    e.target.closest('form').submit();
  }
});

// ---------- Custom confirm modal & toast (replace browser confirm()/alert()) ----------

function adminConfirm(message, confirmLabel) {
  return new Promise((resolve) => {
    const overlay = document.createElement('div');
    overlay.className = 'admin-modal-overlay';
    overlay.innerHTML =
      '<div class="admin-modal" role="alertdialog" aria-modal="true">' +
      '<p class="admin-modal-message"></p>' +
      '<div class="admin-modal-actions">' +
      '<button type="button" class="btn btn-admin-ghost btn-sm" data-action="cancel">Batal</button>' +
      '<button type="button" class="btn btn-admin-danger btn-sm" data-action="confirm"></button>' +
      '</div></div>';
    overlay.querySelector('.admin-modal-message').textContent = message;
    overlay.querySelector('[data-action="confirm"]').textContent = confirmLabel || 'Hapus';
    document.body.appendChild(overlay);

    const done = (result) => {
      document.removeEventListener('keydown', onKeydown);
      overlay.classList.remove('is-open');
      overlay.addEventListener('transitionend', () => overlay.remove(), { once: true });
      resolve(result);
    };
    const onKeydown = (e) => {
      if (e.key === 'Escape') done(false);
    };

    overlay.querySelector('[data-action="cancel"]').addEventListener('click', () => done(false));
    overlay.querySelector('[data-action="confirm"]').addEventListener('click', () => done(true));
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) done(false);
    });
    document.addEventListener('keydown', onKeydown);

    requestAnimationFrame(() => {
      overlay.classList.add('is-open');
      overlay.querySelector('[data-action="cancel"]').focus();
    });
  });
}

let adminToastStack;
function adminToast(message, type) {
  if (!adminToastStack) {
    adminToastStack = document.createElement('div');
    adminToastStack.className = 'admin-toast-stack';
    document.body.appendChild(adminToastStack);
  }
  const toast = document.createElement('div');
  toast.className = 'admin-toast' + (type === 'error' ? ' is-error' : '');
  toast.textContent = message;
  adminToastStack.appendChild(toast);
  requestAnimationFrame(() => toast.classList.add('is-open'));
  setTimeout(() => {
    toast.classList.remove('is-open');
    toast.addEventListener('transitionend', () => toast.remove(), { once: true });
  }, 4000);
}

// Forms with data-confirm="..." show the custom modal instead of window.confirm()
// before submitting (e.g. delete buttons).
document.addEventListener('submit', function (e) {
  const form = e.target;
  if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) return;
  if (form.dataset.confirmed === 'true') return;

  e.preventDefault();
  adminConfirm(form.getAttribute('data-confirm')).then((ok) => {
    if (ok) {
      form.dataset.confirmed = 'true';
      form.submit();
    }
  });
});

document.querySelectorAll('.dropzone').forEach(function (dropzone) {
  const input = document.getElementById(dropzone.getAttribute('for'));
  if (!input) return;

  ['dragenter', 'dragover'].forEach((evt) => {
    dropzone.addEventListener(evt, (e) => {
      e.preventDefault();
      dropzone.classList.add('is-dragover');
    });
  });

  ['dragleave', 'drop'].forEach((evt) => {
    dropzone.addEventListener(evt, (e) => {
      e.preventDefault();
      dropzone.classList.remove('is-dragover');
    });
  });

  dropzone.addEventListener('drop', (e) => {
    const file = e.dataTransfer.files[0];
    if (file) uploadImage(file, input);
  });
});

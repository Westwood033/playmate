/**
 * image-upload.js
 * Gère le widget d'upload d'images :
 *   - Drag & drop + clic sur zone
 *   - Preview immédiat
 *   - Suppression individuelle
 *   - Réordonnancement drag & drop entre miniatures
 *   - Badge "Couverture" sur la première image
 *   - Synchronisation avec l'input file Symfony
 */

(function () {
  'use strict';

  // ── Sélecteurs ──────────────────────────────────────────────────────────────
  const dropZone   = document.getElementById('upload-drop-zone');
  const fileInput  = document.getElementById('item_imageFiles');   // généré par Symfony
  const previewGrid = document.getElementById('upload-preview-grid');
  const countLabel  = document.getElementById('upload-count');

  if (!dropZone || !fileInput || !previewGrid) return;

  // ── État interne ─────────────────────────────────────────────────────────────
  // On stocke des objets { file: File, dataUrl: string, id: string }
  let files = [];

  const MAX_FILES = 8;
  const MAX_SIZE  = 5 * 1024 * 1024; // 5 Mo

  // ── Helpers ──────────────────────────────────────────────────────────────────
  function uid() {
    return Math.random().toString(36).slice(2, 9);
  }

  function updateCount() {
    if (countLabel) {
      countLabel.textContent = files.length > 0
        ? `${files.length} / ${MAX_FILES} photo${files.length > 1 ? 's' : ''}`
        : '';
    }
  }

  /** Reconstruit un FileList synthétique et l'assigne à l'input Symfony */
  function syncInputFiles() {
    const dt = new DataTransfer();
    files.forEach(f => dt.items.add(f.file));
    fileInput.files = dt.files;
    updateCount();
  }

  /** Crée un élément de miniature pour `entry` et l'insère dans la grille */
  function addThumb(entry) {
    const thumb = document.createElement('div');
    thumb.className = 'upload-thumb';
    thumb.dataset.id = entry.id;
    thumb.draggable = true;

    thumb.innerHTML = `
      <img src="${entry.dataUrl}" alt="Aperçu">
      <button type="button" class="upload-thumb__remove" title="Supprimer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M18 6 6 18M6 6l12 12"/>
        </svg>
      </button>
      <span class="upload-thumb__cover">Couverture</span>
    `;

    // Suppression
    thumb.querySelector('.upload-thumb__remove').addEventListener('click', () => {
      files = files.filter(f => f.id !== entry.id);
      thumb.remove();
      syncInputFiles();
      refreshCoverBadge();
    });

    // Drag & drop entre miniatures
    thumb.addEventListener('dragstart', onThumbDragStart);
    thumb.addEventListener('dragover',  onThumbDragOver);
    thumb.addEventListener('dragleave', onThumbDragLeave);
    thumb.addEventListener('drop',      onThumbDrop);
    thumb.addEventListener('dragend',   onThumbDragEnd);

    previewGrid.appendChild(thumb);
    refreshCoverBadge();
  }

  /** Met à jour le badge "Couverture" : seul le premier thumb l'affiche */
  function refreshCoverBadge() {
    const thumbs = previewGrid.querySelectorAll('.upload-thumb');
    thumbs.forEach((t, i) => {
      t.classList.toggle('upload-thumb--cover', i === 0);
    });
  }

  /** Traite un tableau de File objects (filtre, lit, crée les thumbs) */
  function processFiles(newFiles) {
    const remaining = MAX_FILES - files.length;
    if (remaining <= 0) {
      showError(`Maximum ${MAX_FILES} photos autorisées.`);
      return;
    }

    const toAdd = [...newFiles].slice(0, remaining);
    const errors = [];

    toAdd.forEach(file => {
      if (!file.type.startsWith('image/')) {
        errors.push(`"${file.name}" : format non supporté.`);
        return;
      }
      if (file.size > MAX_SIZE) {
        errors.push(`"${file.name}" : trop volumineux (max 5 Mo).`);
        return;
      }

      const entry = { file, dataUrl: null, id: uid() };
      files.push(entry);

      // Lecture asynchrone pour la preview
      const reader = new FileReader();
      reader.onload = e => {
        entry.dataUrl = e.target.result;
        addThumb(entry);
        syncInputFiles();
      };
      reader.readAsDataURL(file);
    });

    if (errors.length) showError(errors.join('\n'));
  }

  function showError(msg) {
    let el = document.getElementById('upload-error');
    if (!el) {
      el = document.createElement('p');
      el.id = 'upload-error';
      el.className = 'upload-error';
      dropZone.parentNode.insertBefore(el, dropZone.nextSibling);
    }
    el.textContent = msg;
    setTimeout(() => el && (el.textContent = ''), 4000);
  }

  // ── Zone de drop ─────────────────────────────────────────────────────────────
  dropZone.addEventListener('click', () => fileInput.click());

  dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.classList.add('upload-drop-zone--active');
  });

  dropZone.addEventListener('dragleave', e => {
    if (!dropZone.contains(e.relatedTarget)) {
      dropZone.classList.remove('upload-drop-zone--active');
    }
  });

  dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('upload-drop-zone--active');
    processFiles(e.dataTransfer.files);
  });

  fileInput.addEventListener('change', () => {
    processFiles(fileInput.files);
    // Reset l'input pour permettre de sélectionner les mêmes fichiers à nouveau
    fileInput.value = '';
  });

  // ── Drag & drop entre miniatures ─────────────────────────────────────────────
  let dragSrcId = null;

  function onThumbDragStart(e) {
    dragSrcId = this.dataset.id;
    this.classList.add('upload-thumb--dragging');
    e.dataTransfer.effectAllowed = 'move';
  }

  function onThumbDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    if (this.dataset.id !== dragSrcId) {
      this.classList.add('upload-thumb--dragover');
    }
  }

  function onThumbDragLeave() {
    this.classList.remove('upload-thumb--dragover');
  }

  function onThumbDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove('upload-thumb--dragover');

    if (this.dataset.id === dragSrcId) return;

    // Réordonner le tableau `files`
    const srcIdx  = files.findIndex(f => f.id === dragSrcId);
    const destIdx = files.findIndex(f => f.id === this.dataset.id);
    if (srcIdx < 0 || destIdx < 0) return;

    const [moved] = files.splice(srcIdx, 1);
    files.splice(destIdx, 0, moved);

    // Réordonner le DOM
    const thumbs   = [...previewGrid.querySelectorAll('.upload-thumb')];
    const srcThumb  = thumbs.find(t => t.dataset.id === dragSrcId);
    const destThumb = thumbs.find(t => t.dataset.id === this.dataset.id);

    if (srcIdx < destIdx) {
      destThumb.after(srcThumb);
    } else {
      destThumb.before(srcThumb);
    }

    syncInputFiles();
    refreshCoverBadge();
  }

  function onThumbDragEnd() {
    this.classList.remove('upload-thumb--dragging');
    previewGrid.querySelectorAll('.upload-thumb').forEach(t =>
      t.classList.remove('upload-thumb--dragover')
    );
  }

})();

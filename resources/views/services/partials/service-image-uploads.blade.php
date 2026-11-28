    const imageInput = document.getElementById('imageInput');
    const imagePickerPrompt = document.getElementById('imagePickerPrompt');
    const imageCapacity = document.getElementById('imageCapacity');
    const previewContainer = document.getElementById('previewContainer');
    const imageError = document.getElementById('imageError');
    const imageSelectionNotice = document.getElementById('imageSelectionNotice');
    const primaryImageIndex = document.getElementById('primaryImageIndex');
    const temporaryImageInputs = document.getElementById('temporaryImageInputs');
    const uploadStatus = document.getElementById('photoUploadStatus');
    const galleryTotal = document.getElementById('photoGalleryTotal');
    const uploadProgress = document.getElementById('photoUploadProgress');
    const submitOverlay = document.getElementById('submitOverlay');
    const csrfToken = wizardForm.elements.namedItem('_token').value;
    const editGallery = @js(isset($galleryRevision));
    const previewEndpoint = @js(isset($galleryRevision) ? route('services.edit', $service->id) : route('services.create'));
    const submitAction = editGallery ? 'Salvează' : 'Publică';
    const maxImages = {{ \App\Support\ServiceImageStorage::MAX_IMAGES }};
    const maxImageMiB = {{ \App\Support\ServiceImageStorage::MAX_IMAGE_MIB }};
    const maxImageBytes = maxImageMiB * 1024 * 1024;
    const photos = [];
    let primaryPhoto = null;
    let activeUploads = 0;
    let discarding = false;
    let submitting = false;

    function setImageError(message = '') {
        imageError.textContent = message;
        imageError.classList.toggle('hidden', !message);
    }

    function refreshPhotoControls() {
        let ready = 0;
        let failed = 0;
        let progress = 0;
        photos.forEach((photo, index) => {
            const uploaded = photo.state === 'ready';
            const removing = photo.state === 'removing';
            if (uploaded && !photo.existing) ready++;
            if (photo.state === 'failed' || photo.state === 'remove-failed') failed++;
            if (!photo.existing) progress += uploaded ? 100 : photo.progress;

            photo.card.querySelector('img').alt = `Poza ${index + 1}`;
            photo.cover.hidden = photo !== primaryPhoto;
            photo.setCover.hidden = photo === primaryPhoto || removing;
            photo.remove.disabled = removing;
            photo.retry.hidden = photo.state !== 'failed';
            if (editGallery) {
                photo.moveUp.disabled = index === 0 || removing;
                photo.moveDown.disabled = index === photos.length - 1 || removing;
            }
            photo.status.textContent = {
                queued: 'În așteptare',
                uploading: photo.progress === 100 ? 'Se salvează poza…' : `Se încarcă · ${photo.progress}%`,
                ready: photo.existing ? 'În galeria publicată' : 'Încărcată',
                failed: photo.error,
                removing: 'Se elimină…',
                'remove-failed': 'Eliminarea a eșuat. Apasă din nou ×.',
            }[photo.state];
        });

        const total = photos.filter((photo) => !photo.existing).length;
        const uploadsIncomplete = ready !== total;
        const remaining = Math.max(0, maxImages - photos.length);
        imageInput.disabled = submitting || remaining === 0;
        const prompt = remaining ? 'Click sau trage poze aici' : `Ai atins limita de ${maxImages} de poze`;
        const capacity = remaining
            ? `${photos.length}/${maxImages} poze · Locuri disponibile ${remaining}`
            : 'Elimină o poză din selecție ca să faci loc alteia.';
        if (imagePickerPrompt.textContent !== prompt) imagePickerPrompt.textContent = prompt;
        if (imageCapacity.textContent !== capacity) imageCapacity.textContent = capacity;
        submitBtn.disabled = submitting || uploadsIncomplete || photos.length > maxImages;
        const galleryLabel = uploadsIncomplete ? 'Total selectat' : 'Total galerie';
        const totalText = `${galleryLabel} ${photos.length}/${maxImages}`;
        if (galleryTotal.textContent !== totalText) galleryTotal.textContent = totalText;
        galleryTotal.classList.toggle('hidden', !editGallery && photos.length === 0);
        uploadProgress.classList.toggle('hidden', !uploadsIncomplete);
        uploadProgress.value = total ? Math.round(progress / total) : 0;
        let status = '';
        if (photos.length > maxImages) {
            status = `Păstrează maximum ${maxImages} de poze în galerie.`;
        } else if (failed) {
            status = `${ready} din ${total} poze noi încărcate. Reîncearcă sau elimină pozele cu erori ca să poți apăsa ${submitAction}.`;
        } else if (photos.some((photo) => photo.state === 'removing')) {
            status = `Se elimină poza. Poți apăsa ${submitAction} după ce se termină.`;
        } else if (total && ready === total) {
            const added = total === 1 ? '1 imagine nouă' : `${total}${total >= 20 ? ' de' : ''} imagini noi`;
            status = `Ai adăugat ${added}. ${editGallery ? 'Poți salva modificările.' : 'Poți publica anunțul.'}`;
        } else if (total) {
            status = `${ready} din ${total} poze încărcate. Poți apăsa ${submitAction} după ce se termină încărcarea.`;
        }
        if (uploadStatus.textContent !== status) uploadStatus.textContent = status;
    }

    function makePhoto({ file = null, id = null, existing = null, src }) {
        const card = document.createElement('div');
        card.className = 'overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-[#444] dark:bg-[#252525]';
        card.innerHTML = `
            <div class="relative aspect-square bg-slate-100 dark:bg-[#333]">
                <img class="h-full w-full object-cover" alt="" loading="lazy" decoding="async">
                <span data-cover class="absolute left-2 top-2 rounded-full bg-white px-2 py-1 text-xs font-bold text-slate-900" hidden>Principală</span>
                <button data-set-cover type="button" title="Setează ca principală" aria-label="Setează ca principală" class="absolute left-2 top-2 h-8 w-8 rounded-full bg-white font-bold text-slate-900">★</button>
                <button data-remove type="button" title="Elimină poza" aria-label="Elimină poza" class="absolute right-2 top-2 h-8 w-8 rounded-full bg-white font-bold text-slate-900 disabled:opacity-50">×</button>
            </div>
            <div class="space-y-1 px-2 py-2 text-xs text-slate-700 dark:text-slate-200">
                <p data-status></p>
                <button data-retry type="button" class="font-bold text-[#C81424] underline dark:text-red-300" hidden>Reîncearcă</button>
                ${editGallery ? `<div class="flex gap-2">
                    <button data-up type="button" aria-label="Mută poza mai devreme" title="Mută poza mai devreme" class="rounded border px-2 py-1 disabled:opacity-50">↑</button>
                    <button data-down type="button" aria-label="Mută poza mai târziu" title="Mută poza mai târziu" class="rounded border px-2 py-1 disabled:opacity-50">↓</button>
                </div>` : ''}
            </div>`;
        card.querySelector('img').src = src;
        const photo = {
            card, file, id, existing, src,
            state: id || existing ? 'ready' : 'queued', progress: id || existing ? 100 : 0,
            cover: card.querySelector('[data-cover]'), setCover: card.querySelector('[data-set-cover]'),
            retry: card.querySelector('[data-retry]'), remove: card.querySelector('[data-remove]'),
            status: card.querySelector('[data-status]'),
            moveUp: card.querySelector('[data-up]'), moveDown: card.querySelector('[data-down]'),
        };
        photo.setCover.addEventListener('click', () => {
            if (editGallery) {
                movePhoto(photo, 0);
                return;
            }
            primaryPhoto = photo;
            refreshPhotoControls();
        });
        photo.moveUp?.addEventListener('click', () => movePhoto(photo, photos.indexOf(photo) - 1));
        photo.moveDown?.addEventListener('click', () => movePhoto(photo, photos.indexOf(photo) + 1));
        photo.retry.addEventListener('click', () => {
            photo.state = 'queued';
            photo.progress = 0;
            pumpUploads();
        });
        photo.remove.addEventListener('click', () => {
            setImageError();
            if (photo.id || photo.state === 'uploading') {
                // Let the upload finish so its server ID can be discarded as well.
                photo.state = 'removing';
            } else {
                forgetPhoto(photo);
            }
            pumpUploads();
        });
        photos.push(photo);
        previewContainer.appendChild(card);
        primaryPhoto ??= photo;
        return photo;
    }

    function movePhoto(photo, index) {
        photos.splice(photos.indexOf(photo), 1);
        photos.splice(index, 0, photo);
        previewContainer.insertBefore(photo.card, photos[index + 1]?.card ?? null);
        primaryPhoto = photos[0];
        refreshPhotoControls();
    }

    function forgetPhoto(photo) {
        photos.splice(photos.indexOf(photo), 1);
        photo.card.remove();
        if (photo.file) URL.revokeObjectURL(photo.src);
        if (primaryPhoto === photo) primaryPhoto = photos[0] ?? null;
    }

    function sendPhotoRequest(fields, onProgress) {
        return new Promise((resolve, reject) => {
            const request = new XMLHttpRequest();
            const data = new FormData();
            data.append('_token', csrfToken);
            if (editGallery) data.append('_method', 'PUT');
            Object.entries(fields).forEach(([name, value]) => data.append(name, value));
            request.open('POST', wizardForm.action);
            request.setRequestHeader('Accept', 'application/json');
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.responseType = 'json';
            request.timeout = 180000;
            if (onProgress) {
                request.upload.onprogress = (event) => {
                    if (event.lengthComputable) onProgress(Math.round(100 * event.loaded / event.total));
                };
            }
            request.onload = () => {
                if (request.status >= 200 && request.status < 300) {
                    resolve(request.response);
                } else {
                    const message = request.response?.errors?.image?.[0];
                    reject(new Error(message || (request.status === 413 ? 'Fișierul depășește limita serverului.' : 'Cererea nu a reușit.')));
                }
            };
            request.onerror = request.ontimeout = () => reject(new Error('Conexiunea a fost întreruptă.'));
            request.send(data);
        });
    }

    async function uploadPhoto(photo) {
        photo.state = 'uploading';
        activeUploads++;
        try {
            const result = await sendPhotoRequest({ upload_temporary: '1', image: photo.file }, (progress) => {
                photo.progress = progress;
                if (photo.state === 'uploading') {
                    photo.status.textContent = progress === 100 ? 'Se salvează poza…' : `Se încarcă · ${progress}%`;
                    const uploads = photos.filter((item) => !item.existing);
                    uploadProgress.value = Math.round(uploads.reduce((sum, item) => sum + item.progress, 0) / uploads.length);
                }
            });
            if (!/^[a-f0-9]{64}$/.test(result?.id ?? '')) throw new Error('Serverul nu a confirmat încărcarea.');
            photo.id = result.id;
            photo.progress = 100;
            if (photo.state !== 'removing') {
                photo.state = 'ready';
                const duplicates = photos.filter((item) => item.state === 'ready' && item.id === photo.id);
                duplicates.slice(1).forEach((duplicate) => {
                    if (primaryPhoto === duplicate) primaryPhoto = duplicates[0];
                    forgetPhoto(duplicate);
                });
            }
        } catch (error) {
            if (photo.state === 'removing') {
                forgetPhoto(photo);
            } else {
                photo.progress = 0;
                photo.state = 'failed';
                photo.error = error.message;
            }
        } finally {
            activeUploads--;
            pumpUploads();
        }
    }

    async function discardPhoto(photo) {
        discarding = true;
        try {
            if (!photos.some((item) => item !== photo && item.state === 'ready' && item.id === photo.id)) {
                await sendPhotoRequest({ remove_temporary: photo.id });
            }
            forgetPhoto(photo);
        } catch {
            photo.state = 'remove-failed';
            setImageError('Poza nu a putut fi eliminată. Apasă din nou pe butonul de eliminare.');
        } finally {
            discarding = false;
            pumpUploads();
        }
    }

    function pumpUploads() {
        if (!discarding) {
            const removing = photos.find((photo) => photo.state === 'removing');
            if (removing) {
                if (activeUploads === 0) discardPhoto(removing);
            } else {
                while (activeUploads < 3) {
                    const next = photos.find((photo) => photo.state === 'queued');
                    if (!next) break;
                    uploadPhoto(next);
                }
            }
        }
        refreshPhotoControls();
    }

    imageInput.addEventListener('change', function () {
        const files = Array.from(this.files ?? []);
        this.value = '';
        setImageError();
        imageSelectionNotice.textContent = '';
        const availableSlots = Math.max(0, maxImages - photos.length);
        const acceptedFiles = files.slice(0, availableSlots);
        const invalid = acceptedFiles.find((file) => file.size > maxImageBytes || (file.type && !/^image\/(jpeg|png|webp)$/.test(file.type)));
        if (invalid) {
            setImageError(invalid.size > maxImageBytes
                ? `„${invalid.name}” depășește limita de ${maxImageMiB} MB.`
                : `„${invalid.name}” trebuie să fie o imagine JPG, PNG sau WebP.`);
            return;
        }
        if (acceptedFiles.length < files.length) {
            const message = acceptedFiles.length
                ? `Am adăugat ${acceptedFiles.length} din ${files.length} în ordinea selecției. Restul depășesc limita galeriei.`
                : `Ai atins limita de ${maxImages} de poze. Selecția nouă nu a fost adăugată.`;
            imageSelectionNotice.textContent = `${message} Poți apăsa ${submitAction} după ce se încarcă pozele păstrate. Ca să adaugi altele, elimină întâi din cele păstrate.`;
        }
        acceptedFiles.forEach((file) => makePhoto({ file, src: URL.createObjectURL(file) }));
        pumpUploads();
    });

    const restoredIds = @js($temporaryImageIds ?? []);
    const previousIds = @js((array) old('temporary_image_ids', []));
    const previousCover = previousIds[@js(old('primary_image_index', 0))];
    restoredIds.forEach((id) => {
        const url = new URL(previewEndpoint);
        url.searchParams.set('temporary_image', id);
        const photo = makePhoto({ id, src: url.href });
        if (id === previousCover) primaryPhoto = photo;
    });
    if (Array.isArray(previousIds) && restoredIds.length < previousIds.length) {
        setImageError('Unele poze nu mai sunt disponibile. Alege din nou pozele care lipsesc.');
    }
    const galleryPhotos = @js($galleryPhotos ?? []);
    galleryPhotos.forEach(({ token, src }) => {
        if (src) {
            makePhoto({ existing: token, src });
        } else {
            const id = token.slice(4);
            const url = new URL(previewEndpoint);
            url.searchParams.set('temporary_image', id);
            makePhoto({ id, src: url.href });
        }
    });
    if (@js($galleryDraftMissingPhotos ?? false)) {
        setImageError('Unele poze nu mai sunt disponibile. Alege din nou pozele care lipsesc.');
    }
    refreshPhotoControls();

    wizardForm.addEventListener('submit', (event) => {
        if (submitBtn.disabled || !validateAllSteps()) {
            event.preventDefault();
            return;
        }
        temporaryImageInputs.replaceChildren(...photos.map((photo) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = editGallery ? 'gallery_order[]' : 'temporary_image_ids[]';
            input.value = editGallery ? (photo.existing ?? `new:${photo.id}`) : photo.id;
            return input;
        }));
        if (primaryImageIndex) primaryImageIndex.value = primaryPhoto ? String(photos.indexOf(primaryPhoto)) : '';
        submitting = true;
        refreshPhotoControls();
        submitBtn.textContent = 'Se salvează…';
        submitOverlay.classList.remove('hidden');
        submitOverlay.classList.add('flex');
    });

/**
 * Lesson completion: automatic tracking + manual "Mark as complete" button.
 * YouTube uses a thumbnail + play button, then mounts an embed on click.
 */
function youtubeEmbedSrc(videoId, origin) {
    const params = new URLSearchParams({
        autoplay: '1',
        rel: '0',
        modestbranding: '1',
        playsinline: '1',
        enablejsapi: '1',
    });

    if (origin) {
        params.set('origin', origin);
    }

    return `https://www.youtube.com/embed/${videoId}?${params.toString()}`;
}

function initYouTubePlayer(root, onEnded) {
    const mount = root.querySelector('[data-youtube-player]');
    if (!mount) {
        return;
    }

    const videoId = mount.dataset.youtubeId;
    const playBtn = mount.querySelector('[data-youtube-play]');
    const frameHost = mount.querySelector('[data-youtube-frame]');
    if (!videoId || !playBtn || !frameHost) {
        return;
    }

    let started = false;

    const handleMessage = (event) => {
        if (!started) {
            return;
        }
        if (event.origin !== 'https://www.youtube.com' && event.origin !== 'https://www.youtube-nocookie.com') {
            return;
        }

        let data = event.data;
        if (typeof data === 'string') {
            try {
                data = JSON.parse(data);
            } catch {
                return;
            }
        }

        // playerState 0 === ended
        const state = data?.info?.playerState ?? data?.info;
        if (data?.event === 'infoDelivery' && state === 0) {
            onEnded?.();
        }
    };

    window.addEventListener('message', handleMessage);

    playBtn.addEventListener('click', () => {
        if (started) {
            return;
        }
        started = true;

        const iframe = document.createElement('iframe');
        iframe.id = 'lesson-youtube-player';
        iframe.className = 'h-full w-full';
        iframe.src = youtubeEmbedSrc(videoId, window.location.origin);
        iframe.title = 'Lesson video';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');

        frameHost.innerHTML = '';
        frameHost.appendChild(iframe);
        frameHost.classList.remove('hidden');
        playBtn.remove();

        // Tell the player we want API events (ended).
        iframe.addEventListener('load', () => {
            try {
                iframe.contentWindow?.postMessage(JSON.stringify({ event: 'listening', id: videoId }), '*');
            } catch {
                // Ignore cross-origin postMessage failures.
            }
        });
    });
}

function initLessonProgress(root) {
    if (!root) {
        return;
    }

    const completeUrl = root.dataset.completeUrl;
    const lessonType = root.dataset.lessonType;
    const alreadyCompleted = root.dataset.alreadyCompleted === '1';
    let recorded = alreadyCompleted;
    const pageOpenedAt = Date.now();
    const minDwellMs = 5000;

    const control = document.getElementById('lesson-complete-control');
    const incompleteBlock = control?.querySelector('[data-lesson-complete-incomplete]');
    const doneBlock = control?.querySelector('[data-lesson-complete-done]');
    const completeBtn = root.querySelector('[data-lesson-complete-btn]');
    const completeForm = root.querySelector('[data-lesson-complete-form]');
    const statusEl = document.getElementById('lesson-progress-status');

    function showCompletedUI(message) {
        root.dataset.alreadyCompleted = '1';
        if (control) {
            control.dataset.completed = '1';
        }
        incompleteBlock?.remove();
        if (doneBlock) {
            doneBlock.classList.remove('hidden');
            doneBlock.textContent = `✓ ${message ?? 'Completed'}`;
        } else {
            const span = document.createElement('span');
            span.dataset.lessonCompleteDone = '';
            span.className = 'inline-flex items-center gap-2 text-success-dark font-medium';
            span.textContent = `✓ ${message ?? 'Completed'}`;
            control?.appendChild(span);
        }
    }

    function setStatus(message) {
        if (!statusEl || recorded) {
            return;
        }
        statusEl.textContent = message;
    }

    async function recordComplete(source = 'auto') {
        if (recorded) {
            return;
        }

        if (completeBtn) {
            completeBtn.disabled = true;
            completeBtn.textContent = source === 'manual' ? 'Saving…' : 'Mark as complete';
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token) {
            recorded = false;
            if (completeBtn) {
                completeBtn.disabled = false;
            }
            return;
        }

        try {
            const response = await fetch(completeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                recorded = false;
                if (completeBtn) {
                    completeBtn.disabled = false;
                    completeBtn.textContent = 'Mark as complete';
                }
                setStatus('Could not save progress. Try the button again.');
                return;
            }

            const data = await response.json();
            recorded = true;
            showCompletedUI('Completed');

            document.querySelectorAll(`[data-lesson-id="${root.dataset.lessonId}"]`).forEach((el) => {
                if (el.dataset.role === 'checkmark') {
                    el.classList.remove('hidden');
                }
            });

            const progressBar = document.querySelector('[data-course-progress-bar]');
            const progressLabel = document.querySelector('[data-course-progress-label]');
            if (progressBar && data.completed_count != null && data.total_lessons > 0) {
                const pct = Math.min(100, Math.round((data.completed_count / data.total_lessons) * 100));
                progressBar.style.width = `${pct}%`;
            }
            if (progressLabel && data.completed_count != null && data.total_lessons != null) {
                progressLabel.textContent = `${data.completed_count} / ${data.total_lessons}`;
            }
        } catch {
            recorded = false;
            if (completeBtn) {
                completeBtn.disabled = false;
                completeBtn.textContent = 'Mark as complete';
            }
            setStatus('Could not save progress. Try the button again.');
        }
    }

    completeForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        recordComplete('manual');
    });

    if (lessonType === 'youtube') {
        initYouTubePlayer(root, () => recordComplete('auto'));
    }

    if (alreadyCompleted && lessonType !== 'youtube') {
        return;
    }

    // Completed YouTube lessons still get a playable player above;
    // skip auto-tracking listeners only.
    if (alreadyCompleted) {
        return;
    }

    function canCompleteByEngagement() {
        return Date.now() - pageOpenedAt >= minDwellMs;
    }

    const video = root.querySelector('video[data-lesson-video]');
    if (video) {
        video.addEventListener('ended', () => recordComplete('auto'));
        video.addEventListener('timeupdate', () => {
            if (video.duration > 0 && video.currentTime / video.duration >= 0.9) {
                recordComplete('auto');
            }
        });
    }

    const scrollMarker = root.querySelector('[data-lesson-scroll-marker]');
    if (scrollMarker && lessonType !== 'youtube') {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries.some((e) => e.isIntersecting) && canCompleteByEngagement()) {
                    recordComplete('auto');
                    observer.disconnect();
                }
            },
            { threshold: 0.6 }
        );
        observer.observe(scrollMarker);

        setTimeout(() => {
            if (recorded || !canCompleteByEngagement()) {
                return;
            }
            const content = root.querySelector('[data-lesson-content]');
            if (content && content.scrollHeight <= window.innerHeight * 1.1) {
                recordComplete('auto');
            }
        }, minDwellMs + 500);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-lesson-progress]').forEach(initLessonProgress);
});

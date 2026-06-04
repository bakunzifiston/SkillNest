/**
 * Lesson completion: automatic tracking + manual "Mark as complete" button.
 */
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

    const youtubeId = root.dataset.youtubeId;
    if (lessonType === 'youtube' && youtubeId) {
        const mountId = 'lesson-youtube-player';
        if (document.getElementById(mountId)) {
            const loadApi = () =>
                new Promise((resolve) => {
                    if (window.YT?.Player) {
                        resolve();
                        return;
                    }
                    const prev = window.onYouTubeIframeAPIReady;
                    window.onYouTubeIframeAPIReady = () => {
                        prev?.();
                        resolve();
                    };
                    if (!document.querySelector('script[src*="youtube.com/iframe_api"]')) {
                        const tag = document.createElement('script');
                        tag.src = 'https://www.youtube.com/iframe_api';
                        document.head.appendChild(tag);
                    }
                });

            loadApi().then(() => {
                new window.YT.Player(mountId, {
                    videoId: youtubeId,
                    events: {
                        onStateChange: (event) => {
                            if (event.data === window.YT.PlayerState.ENDED) {
                                recordComplete('auto');
                            }
                        },
                    },
                });
            });
        }
    }

    const scrollMarker = root.querySelector('[data-lesson-scroll-marker]');
    if (scrollMarker) {
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
